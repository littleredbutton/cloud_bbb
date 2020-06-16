import React, { useState, useEffect } from 'react';
import { api, ShareWith, ShareType, RoomShare, Room, Permission } from './Api';
import './ShareWith.scss';

type Props = {
	room: Room;
	permission: Permission.User | Permission.Moderator;
	shares: RoomShare[] | undefined;
	setShares: (shares: RoomShare[]) => void;
}

const ShareWith: React.FC<Props> = ({ room, permission, shares: allShares, setShares }) => {
	const [search, setSearch] = useState<string>('');
	const [hasFocus, setFocus] = useState<boolean>(false);
	const [recommendations, setRecommendations] = useState<ShareWith>();
	const [searchResults, setSearchResults] = useState<ShareWith>();

	const shares = (allShares && permission === Permission.Moderator) ?
		allShares.filter(share => share.permission !== Permission.User) : allShares;

	const sharedUserIds = shares ? shares.filter(share => share.shareType === ShareType.User).map(share => share.shareWith) : [];
	const sharedGroupIds = shares ? shares.filter(share => share.shareType === ShareType.Group).map(share => share.shareWith) : [];

	useEffect(() => {
		api.searchShareWith(search).then(result => {
			setSearchResults(result);
		});
	}, [search]);

	useEffect(() => {
		api.getRecommendedShareWith().then(result => setRecommendations(result));
	}, []);

	async function addRoomShare(shareWith: string, shareType: number, displayName: string) {
		const roomShare = await api.createRoomShare(room.id, shareType, shareWith, permission);

		roomShare.shareWithDisplayName = displayName;

		console.log('addRoomShare', allShares, roomShare);

		const newShares = allShares ? [...allShares] : [];
		const index = newShares.findIndex(share => share.id === roomShare.id);

		if (index > -1) {
			newShares[index] = roomShare;
		} else {
			newShares.push(roomShare);
		}

		console.log('newroomshares', newShares);

		setShares(newShares);
	}

	async function deleteRoomShare(id: number) {
		console.log('deleteRoomShare', id);

		await api.deleteRoomShare(id);

		setShares((allShares ? [...allShares] : []).filter(share => share.id !== id));
	}

	function renderSearchResults(options: ShareWith) {
		return (
			<ul className="bbb-selection">
				{[
					...options.users.filter(user => !sharedUserIds.includes(user.value.shareWith)),
					...options.groups.filter(group => !sharedGroupIds.includes(group.value.shareWith)),
				].map(option => {
					return (<li key={option.value.shareWith} onClick={() => addRoomShare(option.value.shareWith, option.value.shareType, option.label)}>
						{option.label}{option.value.shareType === ShareType.Group ? ` (${t('bbb', 'Group')})` : ''}
					</li>);
				})}
			</ul>
		);
	}

	function renderShares(shares: RoomShare[]) {
		const currentUser = OC.getCurrentUser();
		const ownShare = {
			id: -1,
			roomId: room.id,
			shareType: ShareType.User,
			shareWith: currentUser.uid,
			shareWithDisplayName: currentUser.displayName,
			permission: Permission.Admin,
		};

		return (
			<ul className="bbb-shareWith">
				{[ownShare, ...shares].map(share => {
					const avatarUrl = share.shareType === ShareType.User ? OC.generateUrl('/avatar/' + encodeURIComponent(share.shareWith) + '/' + 32, {
						user: share.shareWith,
						size: 32,
						requesttoken: OC.requestToken,
					}) : undefined;
					const displayName = share.shareWithDisplayName || share.shareWith;

					return (
						<li key={share.id} className="bbb-shareWith__item">
							<div className="avatardiv">
								{avatarUrl && <img src={avatarUrl} alt={`Avatar from ${displayName}`} />}
								{share.shareType === ShareType.Group && <span className="icon-group-white"></span>}
							</div>
							<div className="bbb-shareWith__item__label">
								<h5>{displayName}{(share.permission === Permission.Moderator && permission === Permission.User) ? ` (${t('bbb', 'moderator')})` : ''}</h5>
							</div>
							{share.id > -1 && <div className="bbb-shareWith__item__action">
								<a className="icon icon-delete icon-visible"
									onClick={ev => {ev.preventDefault(); deleteRoomShare(share.id);}}
									title={t('bbb', 'Delete')} />
							</div>}
						</li>
					);
				})}
			</ul>
		);
	}

	const loading = <><span className="icon icon-loading-small icon-visible"></span> {t('bbb', 'Loading')}</>;

	return (
		<>
			{shares ? renderShares(shares) : loading}

			<div className="bbb-selection-container">
				<input
					type="text"
					value={search}
					onChange={ev => setSearch(ev.currentTarget.value)}
					onFocus={() => setFocus(true)}
					onBlur={() => setTimeout(() => setFocus(false), 100)}
					placeholder={t('bbb', 'Name, group, ...')} />
				{hasFocus && (searchResults ? renderSearchResults(searchResults) : (recommendations ? renderSearchResults(recommendations) : loading))}
			</div>
		</>
	);
};

export default ShareWith;
import React, { useState, useEffect } from 'react';
import { api, ShareWith, ShareType, RoomShare, Room, Permission, ShareWithOption } from '../Common/Api';
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
	const [showSearchResults, setShowSearchResults] = useState<boolean>(false);
	const [recommendations, setRecommendations] = useState<ShareWith>();
	const [searchResults, setSearchResults] = useState<ShareWith>();

	const isOwner = room.userId === OC.currentUser;

	const shares = (allShares && permission === Permission.Moderator) ?
		allShares.filter(share => share.permission !== Permission.User) : allShares;

	const sharedUserIds = shares ? shares.filter(share => share.shareType === ShareType.User).map(share => share.shareWith) : [];
	const sharedGroupIds = shares ? shares.filter(share => share.shareType === ShareType.Group).map(share => share.shareWith) : [];

	useEffect(() => {
		setSearchResults(undefined);
		const searchQuery = search;

		api.searchShareWith(searchQuery).then(result => {
			if (searchQuery === search) {
				setSearchResults(result);
			}
		});
	}, [search]);

	useEffect(() => {
		api.getRecommendedShareWith().then(result => setRecommendations(result));
	}, []);

	useEffect(() => {
		setTimeout(() => setShowSearchResults(hasFocus), 100);
	}, [hasFocus]);

	async function addRoomShare(shareWith: string, shareType: number, displayName: string, permission: Permission) {
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
		setSearch('');
	}

	async function deleteRoomShare(id: number) {
		console.log('deleteRoomShare', id);

		await api.deleteRoomShare(id);

		setShares((allShares ? [...allShares] : []).filter(share => share.id !== id));
	}

	async function toggleAdminShare(share: RoomShare) {
		const newPermission = share.permission === Permission.Admin ? Permission.Moderator : Permission.Admin;

		return addRoomShare(share.shareWith, share.shareType, share.shareWithDisplayName || share.shareWith, newPermission);
	}

	function renderSearchResults(options: ShareWith|undefined) {
		const results = options ? [
			...options.users.filter(user => !sharedUserIds.includes(user.value.shareWith)),
			...options.groups.filter(group => !sharedGroupIds.includes(group.value.shareWith)),
		] : [];

		const renderOption = (option: ShareWithOption) => {
			return (<li key={option.value.shareWith} className="suggestion" onClick={() => addRoomShare(option.value.shareWith, option.value.shareType, option.label, permission)}>
				{option.label}{option.value.shareType === ShareType.Group ? ` (${t('bbb', 'Group')})` : ''}
			</li>);
		};

		return (
			<ul className="bbb-selection">
				{!options ?
					<li><span className="icon icon-loading-small icon-visible"></span> {t('bbb', 'Searching')}</li> :
					(
						(results.length === 0 && search) ? <li>{t('bbb', 'No matches')}</li> : results.map(renderOption)
					)}
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
								<h5>{displayName}
									{(share.permission === Permission.Moderator && permission === Permission.User) && ` (${t('bbb', 'moderator')})`}
									{(share.permission === Permission.Admin) && ` (${t('bbb', 'admin')})`}</h5>
							</div>
							{(share.id > -1 && permission === Permission.Moderator && isOwner) && <div className="bbb-shareWith__item__action">
								<a className={`icon icon-shared icon-visible ${share.permission === Permission.Admin ? 'bbb-icon-selected' : 'bbb-icon-unselected'}`}
									onClick={ev => {ev.preventDefault(); toggleAdminShare(share);}}
									title={t('bbb', 'Share')} />
							</div>}
							{(share.id > -1 && isOwner) && <div className="bbb-shareWith__item__action">
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
				{isOwner ? <input
					type="text"
					value={search}
					onChange={ev => setSearch(ev.currentTarget.value)}
					onFocus={() => setFocus(true)}
					onBlur={() => setFocus(false)}
					placeholder={t('bbb', 'Name, group, ...')} /> :
					<em><span className="icon icon-details icon-visible"></span> {t('bbb', 'You are not allowed to change this option, because this room is shared with you.')}</em>}
				{showSearchResults && renderSearchResults((search && searchResults) ? searchResults : ((recommendations && !search) ? recommendations : undefined))}
			</div>
		</>
	);
};

export default ShareWith;

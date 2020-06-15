import React, { useState, useEffect } from 'react';
import { api, ShareWith, ShareType, RoomShare, Room, Permission } from './Api';
import './ShareWith.scss';

type Props = {
    room: Room;
}

const SearchInput: React.FC<Props> = ({ room }) => {
	const [search, setSearch] = useState<string>('');
	const [hasFocus, setFocus] = useState<boolean>(false);
	const [recommendations, setRecommendations] = useState<ShareWith>();
	const [searchResults, setSearchResults] = useState<ShareWith>();
	const [shares, setShares] = useState<RoomShare[]>();

	const userShares = shares ? shares.filter(share => share.shareType === ShareType.User).map(share => share.shareWith) : [];
	const groupShares = shares ? shares.filter(share => share.shareType === ShareType.Group).map(share => share.shareWith) : [];

	useEffect(() => {
		api.getRoomShares(room.id).then(roomShares => {
			setShares(roomShares);
		}).catch(err => {
			console.warn('Could not load room shares.', err);

			setShares([]);
		});
	}, [room.id]);

	useEffect(() => {
		api.searchShareWith(search).then(result => {
			setSearchResults(result);
		});
	}, [search]);

	useEffect(() => {
		api.getRecommendedShareWith().then(result => setRecommendations(result));
	}, []);

	async function addRoomShare(shareWith: string, shareType: number, displayName: string) {
		const roomShare = await api.createRoomShare(room.id, shareType, shareWith, Permission.Moderator);

		roomShare.shareWithDisplayName = displayName;

		setShares([...(shares || []), roomShare]);
	}

	async function deleteRoomShare(id: number) {
		await api.deleteRoomShare(id);

		setShares(shares?.filter(share => share.id !== id));
	}

	function renderSearchResults(options: ShareWith) {
		return (
			<ul className="bbb-selection">
				{[
					...options.users.filter(user => !userShares.includes(user.value.shareWith)),
					...options.groups.filter(group => !groupShares.includes(group.value.shareWith)),
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
							</div>
							<div className="bbb-shareWith__item__label">
								<h5>{displayName}{share.shareType === ShareType.Group ? ` (${t('bbb', 'Group')})` : ''}</h5>
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

export default SearchInput;
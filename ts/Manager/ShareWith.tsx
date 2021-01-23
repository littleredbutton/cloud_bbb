import React from 'react';
import { api, ShareWith, ShareType, RoomShare, Room, Permission } from '../Common/Api';
import './ShareWith.scss';
import ShareSelection from '../Common/ShareSelection';

type Props = {
	room: Room;
	permission: Permission.User | Permission.Moderator;
	shares: RoomShare[] | undefined;
	setShares: (shares: RoomShare[]) => void;
}

const ShareWith: React.FC<Props> = ({ room, permission, shares: allShares, setShares }) => {
	const isOwner = room.userId === OC.currentUser;

	const shares = (allShares && permission === Permission.Moderator) ?
		allShares.filter(share => share.permission !== Permission.User) : allShares;

	const sharedUserIds = shares ? shares.filter(share => share.shareType === ShareType.User).map(share => share.shareWith) : [];
	const sharedGroupIds = shares ? shares.filter(share => share.shareType === ShareType.Group).map(share => share.shareWith) : [];
	const sharedCircleIds = shares ? shares.filter(share => share.shareType === ShareType.Circle).map(share => share.shareWith) : [];

	async function addRoomShare(shareWith: string, shareType: number, displayName: string, permission: Permission) {
		const roomShare = await api.createRoomShare(room.id, shareType, shareWith, permission);

		roomShare.shareWithDisplayName = displayName;

		const newShares = allShares ? [...allShares] : [];
		const index = newShares.findIndex(share => share.id === roomShare.id);

		if (index > -1) {
			newShares[index] = roomShare;
		} else {
			newShares.push(roomShare);
		}

		setShares(newShares);
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

	function getAvatarUrl(userId: string) {
		return OC.generateUrl('/avatar/' + encodeURIComponent(userId) + '/' + 32, {
			user: userId,
			size: 32,
			requesttoken: OC.requestToken,
		});
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
					const avatarUrl = share.shareType === ShareType.User ? getAvatarUrl(share.shareWith) : undefined;
					const displayName = share.shareWithDisplayName || share.shareWith;

					return (
						<li key={share.id} className="bbb-shareWith__item">
							<div className="avatardiv">
								{avatarUrl && <img src={avatarUrl} alt={`Avatar from ${displayName}`} />}
								{share.shareType === ShareType.Group && <span className="icon-group-white"></span>}
								{share.shareType === ShareType.Circle && <span className="icon-circle-white"></span>}
							</div>
							<div className="bbb-shareWith__item__label">
								<h5>{displayName}
									{(share.permission === Permission.Moderator && permission === Permission.User) && ` (${t('bbb', 'moderator')})`}
									{(share.permission === Permission.Admin) && ` (${t('bbb', 'admin')})`}</h5>
							</div>
							{(share.id > -1 && permission === Permission.Moderator && isOwner) && <div className="bbb-shareWith__item__action">
								<button className="action-item"
									onClick={ev => {
										ev.preventDefault();
										toggleAdminShare(share);
									}}
									title={t('bbb', 'Share')}>
									<span className={`icon icon-shared icon-visible ${share.permission === Permission.Admin ? 'bbb-icon-selected' : 'bbb-icon-unselected'}`}></span>
								</button>
							</div>}
							{(share.id > -1 && isOwner) && <div className="bbb-shareWith__item__action">
								<button className="action-item"
									onClick={ev => {ev.preventDefault(); deleteRoomShare(share.id);}}
									title={t('bbb', 'Delete')}>
									<span className="icon icon-delete icon-visible"></span>
								</button>
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

			{isOwner ?
				<ShareSelection
					selectShare={(shareOption) => addRoomShare(shareOption.value.shareWith, shareOption.value.shareType, shareOption.label, permission)}
					excluded={{userIds: sharedUserIds, groupIds: sharedGroupIds, circleIds: sharedCircleIds}}
					shareType={[ShareType.User, ShareType.Group, ShareType.Circle]}/> :
				<em>
					<span className="icon icon-details icon-visible"></span> {t('bbb', 'You are not allowed to change this option, because this room is shared with you.')}
				</em>
			}
		</>
	);
};

export default ShareWith;

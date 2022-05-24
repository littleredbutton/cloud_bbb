import React from 'react';
import { Room } from '../Common/Api';

type Props = {
    id: string
    room: Room
    updateRoom: (Room) => Promise<void>
}

const SharedPresentationInput: React.FC<Props> = ({ room, updateRoom, id }) => {

	function filepicker() {
		OC.dialogs.filepicker(t('bbb', 'Default Presentation'), file => {
			updateRoom({...room, presentationUserId: '', presentationPath: file});
		},
		);
	}

	function removeFile() {
		updateRoom({...room, presentationUserId: '', presentationPath: ''});
	}

	function getAvatarUrl() {
		if (room.presentationUserId === null || room.presentationUserId === undefined) {
			return ;
		}

		return (OC.generateUrl('/avatar/' + encodeURIComponent(room.presentationUserId) + '/' + 24, {
			user: room.presentationUserId,
			size: 24,
			requesttoken: OC.requestToken,
		}));
	}

	return(
		<div className="bbb-presentation-input">
			<input id={id} type="button" value={t('bbb', 'Choose a File')} onClick={filepicker} />
			<p className={ room.presentationPath === '' ? 'hidden' : ''}>
				<img src={getAvatarUrl()} alt={room.presentationUserId} className="bbb-avatar" height="100%" />
				<em>{room.presentationPath}</em>
				<button onClick={removeFile}><span className="icon icon-close icon-visible"></span></button>
			</p>
		</div>
	);
};

export default SharedPresentationInput;

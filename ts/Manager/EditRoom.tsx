import React, { useState } from 'react';
import { Room, Restriction } from '../Common/Api';
import EditRoomDialog from './EditRoomDialog';

type Props = {
	room: Room;
	restriction?: Restriction;
	updateProperty: (key: string, value: string | boolean | number | null) => Promise<void>;
	updateRoom: (Room) => Promise<void>;
}

const EditRoom: React.FC<Props> = ({ room, restriction, updateProperty, updateRoom }) => {
	const [open, setOpen] = useState<boolean>(false);

	return (
		<>
			<button onClick={ev => { ev.preventDefault(), setOpen(true); }}
				title={t('bbb', 'Edit')} className="action-item">
				<span className="icon icon-settings-dark icon-visible"></span>
			</button>

			<EditRoomDialog room={room} restriction={restriction} updateProperty={updateProperty} updateRoom={updateRoom} open={open} setOpen={setOpen} />
		</>
	);
};

export default EditRoom;

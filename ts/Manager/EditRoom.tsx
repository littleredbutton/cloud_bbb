import React, { useState } from 'react';
import { Room } from './Api';
import EditRoomDialog from './EditRoomDialog';

type Props = {
    room: Room;
    updateProperty: (key: string, value: string | boolean | number) => Promise<void>;
}

const EditRoom: React.FC<Props> = ({ room, updateProperty }) => {
	const [open, setOpen] = useState<boolean>(false);

	return (
		<>
			<a className="icon icon-edit icon-visible"
				onClick={ev => { ev.preventDefault(), setOpen(true); }}
				title={t('bbb', 'Edit')} />

			<EditRoomDialog room={room} updateProperty={updateProperty} open={open} setOpen={setOpen} />
		</>
	);
};

export default EditRoom;
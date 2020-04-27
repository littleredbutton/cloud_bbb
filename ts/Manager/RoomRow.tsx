import React, { useState } from 'react';
import {CopyToClipboard} from 'react-copy-to-clipboard';
import { SubmitInput } from './SubmitInput';
import { Room, api } from './Api';

type Props = {
    room: Room;
    updateRoom: (room: Room) => void;
    deleteRoom: (id: number) => void;
}

type EditableValueProps = {
    setValue: (key: string, value: string|number) => void;
    setActive: (key: string) => void;
    active: string;
    field: string;
    value: string;
    type: 'text' | 'number';
}

const EditableValue: React.FC<EditableValueProps> = ({ setValue, setActive, active, field, value, type }) => {
	if (active === field) {
		return <SubmitInput
			autoFocus={true}
			onSubmitValue={(value) => setValue(field, type === 'number' ? parseInt(value):value)}
			onClick={event => event.stopPropagation()}
			initialValue={value}
			type={type}
		/>;
	}

	function onClick(ev) {
		ev.stopPropagation();

		setActive(field);
	}

	return <a className="action-rename" onClick={onClick}>{value}</a>;
};

const RoomRow: React.FC<Props> = (props) => {
	const [activeEdit, setActiveEdit] = useState('');
	const room = props.room;

	function updateRoom(key: string, value: string|boolean|number) {
		props.updateRoom({
			...props.room,
			[key]: value,
		});

		setActiveEdit('');
	}

	function deleteRow(ev: MouseEvent) {
		ev.preventDefault();

		OC.dialogs.confirm(
			t('bbb', 'Are you sure you want to delete "{name}"? This operation can not be undone', { name: room.name }),
			t('bbb', 'Delete "{name}"?', { name: room.name }),
			confirmed => {
				if (confirmed) {
					props.deleteRoom(room.id);
				}
			},
			true
		);
	}

	function edit(field: string, type: 'text' | 'number' = 'text'){
		return <EditableValue field={field} value={room[field]} active={activeEdit} setActive={setActiveEdit} setValue={updateRoom} type={type} />;
	}

	return (
		<tr key={room.id}>
			<td className="share icon-col">
				<CopyToClipboard text={window.location.origin + api.getUrl(`b/${room.uid}`)}>
					<span  className="icon icon-clippy icon-visible copy-to-clipboard" ></span>
				</CopyToClipboard>
			</td>
			<td className="start icon-col">
				<a href={api.getUrl(`b/${room.uid}`)} className="icon icon-play icon-visible" target="_blank" rel="noopener noreferrer"></a>
			</td>
			<td className="name">
				{edit('name')}
			</td>
			<td className="welcome">
				{edit('welcome')}
			</td>
			<td className="max-participants">
				{edit('maxParticipants', 'number')}
			</td>
			<td className="record">
				<input id={`bbb-record-${room.id}`} type="checkbox" className="checkbox" checked={room.record} onChange={(event) => updateRoom('record', event.target.checked)} />
				<label htmlFor={`bbb-record-${room.id}`}></label>
			</td>
			<td className="remove icon-col">
				<a className="icon icon-delete icon-visible"
					onClick={deleteRow as any}
					title="Delete" />
			</td>
		</tr>
	);
};

export default RoomRow;
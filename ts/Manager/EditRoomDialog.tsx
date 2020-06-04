import React, { useState } from 'react';
import Dialog from './Dialog';
import { Room } from './Api';
import { SubmitInput } from './SubmitInput';

const descriptions: { [key: string]: string } = {
	name: t('bbb', 'Descriptive name of this room.'),
	welcome: t('bbb', 'This message is shown to all users in the chat area after they joined.'),
	maxParticipants: t('bbb', 'Sets a limit on the number of participants for this room. Zero means there is no limit.'),
	recording: t('bbb', 'If enabled, the moderator is able to start the recording.'),
};

type Props = {
    room: Room;
    updateProperty: (key: string, value: string | boolean | number) => Promise<void>;
}

const EditRoomDialog: React.FC<Props> = ({ room, updateProperty }) => {
	const [open, setOpen] = useState<boolean>(false);

	function formElement(label: string, field: string, type: 'text' | 'number' = 'text') {
		return (
			<div className="bbb-form-element">
				<label htmlFor={`bbb-${field}`}>
					<h3>{t('bbb', label)}</h3>
				</label>

				<SubmitInput initialValue={room[field]} type={type} name={field} onSubmitValue={value => updateProperty(field, value)} />
				{descriptions[field] && <em>{descriptions[field]}</em>}
			</div>
		);
	}

	return (
		<>
			<a className="icon icon-edit icon-visible"
				onClick={ev => { ev.preventDefault(), setOpen(true); }}
				title={t('bbb', 'Edit')} />

			<Dialog open={open} onClose={() => setOpen(false)} title={t('bbb', 'Edit "{room}"', { room: room.name })}>
				{formElement('Name', 'name')}
				{formElement('Welcome', 'welcome')}
				{formElement('Participant limit', 'maxParticipants', 'number')}

				<div>
					<div>
						<input id={`bbb-record-${room.id}`}
							type="checkbox"
							className="checkbox"
							checked={room.record}
							onChange={(event) => updateProperty('record', event.target.checked)} />
						<label htmlFor={`bbb-record-${room.id}`}>{t('bbb', 'Recording')}</label>
					</div>
					<em>{descriptions.recording}</em>
				</div>
			</Dialog>
		</>
	);
};

export default EditRoomDialog;
import React, { useState } from 'react';
import { Access, Room } from './Api';
import Dialog from './Dialog';
import ShareWith from './ShareWith';
import { SubmitInput } from './SubmitInput';

const descriptions: { [key: string]: string } = {
	name: t('bbb', 'Descriptive name of this room.'),
	welcome: t('bbb', 'This message is shown to all users in the chat area after they joined.'),
	maxParticipants: t('bbb', 'Sets a limit on the number of participants for this room. Zero means there is no limit.'),
	recording: t('bbb', 'If enabled, the moderator is able to start the recording.'),
	access: t('bbb', 'Public: Everyone knowing the link is able to join. Password: Guests have to provide a password. Waiting room: A moderator has to accept every guest before they can join. Internal: Only Nextcloud users can join.'),
};

type Props = {
    room: Room;
    updateProperty: (key: string, value: string | boolean | number) => Promise<void>;
}

const EditRoomDialog: React.FC<Props> = ({ room, updateProperty }) => {
	const [open, setOpen] = useState<boolean>(false);

	function inputElement(label: string, field: string, type: 'text' | 'number' = 'text') {
		return (
			<div className="bbb-form-element">
				<label htmlFor={`bbb-${field}`}>
					<h3>{label}</h3>
				</label>

				<SubmitInput initialValue={room[field]} type={type} name={field} onSubmitValue={value => updateProperty(field, value)} />
				{descriptions[field] && <em>{descriptions[field]}</em>}
			</div>
		);
	}

	function selectElement(label: string, field: string, value: string, options: {[key: string]: string}, onChange: (value: string) => void) {
		return (
			<div className="bbb-form-element">
				<label htmlFor={`bbb-${field}`}>
					<h3>{label}</h3>
				</label>

				<select name={field} value={value} onChange={(event) => onChange(event.target.value)}>
					{Object.keys(options).map(key => {
						const label = options[key];

						return <option key={key} value={key}>{label}</option>;
					})}
				</select>
				{(value === Access.Password && room.password) && <input type="text" readOnly={true} value={room.password} />}
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
				{inputElement(t('bbb', 'Name'), 'name')}
				{inputElement(t('bbb', 'Welcome'), 'welcome')}
				{inputElement(t('bbb', 'Participant limit'), 'maxParticipants', 'number')}

				{selectElement(t('bbb', 'Access'), 'access', room.access, {
					[Access.Public]: t('bbb', 'Public'),
					[Access.Password]: t('bbb', 'Internal + Password protection for guests'),
					[Access.WaitingRoom]: t('bbb', 'Internal + Waiting room for guests'),
					[Access.Internal]: t('bbb', 'Internal'),
					// [Access.InternalRestricted]: t('bbb', 'Restricted'),
				}, (value) => {
					console.log('access', value);
					updateProperty('access', value);
				})}

				<div className="bbb-form-element">
					<label htmlFor={'bbb-moderator'}>
						<h3>Moderator</h3>
					</label>

					<ShareWith room={room} />
				</div>

				<h3>{t('bbb', 'Miscellaneous')}</h3>
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
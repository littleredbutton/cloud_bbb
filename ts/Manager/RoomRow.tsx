import React, { useState, useEffect } from 'react';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { SubmitInput } from './SubmitInput';
import { Room, Recording, api } from './Api';
import RecordingRow from './RecordingRow';

type Props = {
	room: Room;
	updateRoom: (room: Room) => void;
	deleteRoom: (id: number) => void;
}

type EditableValueProps = {
	setValue: (key: string, value: string | number) => void;
	setActive: (key: string) => void;
	active: string;
	field: string;
	value: string;
	type: 'text' | 'number';
}

type RecordingsNumberProps = {
	recordings: null | Recording[];
	showRecordings: boolean;
	setShowRecordings: (showRecordings: boolean) => void;
}

const RecordingsNumber: React.FC<RecordingsNumberProps> = ({ recordings, showRecordings, setShowRecordings }) => {
	if (recordings === null) {
		return <span className="icon icon-loading-small icon-visible"></span>;
	}

	if (recordings.length > 0) {
		return (
			<a onClick={() => setShowRecordings(!showRecordings)}>
				{recordings.length} <span className='sort_arrow'>{showRecordings ? '▼' : '▲'}</span>
			</a>
		);
	}

	return <span>0</span>;
};

const EditableValue: React.FC<EditableValueProps> = ({ setValue, setActive, active, field, value, type }) => {
	if (active === field) {
		return <SubmitInput
			autoFocus={true}
			onSubmitValue={(value) => setValue(field, type === 'number' ? parseInt(value) : value)}
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
	const [recordings, setRecordings] = useState<Recording[] | null>(null);
	const [showRecordings, setShowRecordings] = useState<boolean>(false);
	const room = props.room;
	const areRecordingsLoaded = recordings !== null;

	useEffect(() => {
		if (areRecordingsLoaded) {
			return;
		}

		api.getRecordings(room.uid).then(recordings => {
			setRecordings(recordings);
		}).catch(err => {
			console.warn('Could not request recordings: ' + room.uid, err);

			setRecordings([]);
		});
	}, [areRecordingsLoaded]);

	function updateRoom(key: string, value: string | boolean | number) {
		props.updateRoom({
			...props.room,
			[key]: value,
		});

		setActiveEdit('');
	}

	function deleteRow(ev: MouseEvent) {
		ev.preventDefault();

		OC.dialogs.confirm(
			t('bbb', 'Are you sure you want to delete "{name}"? This operation can not be undone.', { name: room.name }),
			t('bbb', 'Delete "{name}"?', { name: room.name }),
			confirmed => {
				if (confirmed) {
					props.deleteRoom(room.id);
				}
			},
			true
		);
	}

	function storeRoom() {
		OC.dialogs.filepicker(t('bbb', 'Select target folder'), (path: string) => {
			api.storeRoom(room, path).then((filename) => {
				OC.dialogs.info(
					t('bbb', 'Room URL was stored in "{path}" as "{filename}".', { path: path + '/', filename }),
					t('bbb', 'Link stored'),
					() => undefined,
				);
			}).catch(err => {
				console.warn('Could not store room', err);

				OC.dialogs.alert(
					t('bbb', 'URL to room could not be stored.'),
					t('bbb', 'Error'),
					() => undefined
				);
			});
		}, undefined, 'httpd/unix-directory');
	}

	function storeRecording(recording: Recording) {
		OC.dialogs.filepicker(t('bbb', 'Select target folder'), (path: string) => {
			api.storeRecording(recording, path).then((filename) => {
				OC.dialogs.info(
					t('bbb', 'URL to presentation was stored in "{path}" as "{filename}".', { path: path + '/', filename }),
					t('bbb', 'Link stored'),
					() => undefined,
				);
			}).catch(err => {
				console.warn('Could not store recording', err);

				OC.dialogs.alert(
					t('bbb', 'URL to presentation could not be stored.'),
					t('bbb', 'Error'),
					() => undefined
				);
			});
		}, undefined, 'httpd/unix-directory');
	}

	function deleteRecording(recording: Recording) {
		OC.dialogs.confirm(
			t('bbb', 'Are you sure you want to delete the recording from "{startDate}"? This operation can not be undone.', { startDate: (new Date(recording.startTime)).toLocaleString() }),
			t('bbb', 'Delete?'),
			confirmed => {
				if (confirmed) {
					api.deleteRecording(recording.id).then(success => {
						if (!success) {
							OC.dialogs.info(
								t('bbb', 'Could not delete record'),
								t('bbb', 'Error'),
								() => undefined,
							);

							return;
						}

						if (recordings === null) {
							return;
						}

						setRecordings(recordings.filter(r => r.id !== recording.id));
					}).catch(err => {
						console.warn('Could not delete recording', err);

						OC.dialogs.info(
							t('bbb', 'Could not delete record'),
							t('bbb', 'Server error'),
							() => undefined,
						);
					});
				}
			},
			true
		);
	}

	function edit(field: string, type: 'text' | 'number' = 'text') {
		return <EditableValue field={field} value={room[field]} active={activeEdit} setActive={setActiveEdit} setValue={updateRoom} type={type} />;
	}

	return (
		<>
			<tr className={showRecordings ? 'selected-row' : ''}>
				<td className="share icon-col">
					<CopyToClipboard text={window.location.origin + api.getUrl(`b/${room.uid}`)}>
						<span className="icon icon-clippy icon-visible copy-to-clipboard" ></span>
					</CopyToClipboard>
				</td>
				<td className="start icon-col">
					<a href={api.getUrl(`b/${room.uid}`)} className="icon icon-play icon-visible" target="_blank" rel="noopener noreferrer"></a>
				</td>
				<td className="store icon-col">
					<a onClick={() => storeRoom()} className="icon icon-download icon-visible"></a>
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
				<td><RecordingsNumber recordings={recordings} showRecordings={showRecordings} setShowRecordings={setShowRecordings} /></td>
				<td className="remove icon-col">
					<a className="icon icon-delete icon-visible"
						onClick={deleteRow as any}
						title={t('bbb', 'Delete')} />
				</td>
			</tr>
			{showRecordings && <tr className="recordings-row">
				<td colSpan={9}>
					<table>
						<tbody>
							{recordings?.map(recording => <RecordingRow key={recording.id} recording={recording} deleteRecording={deleteRecording} storeRecording={storeRecording} />)}
						</tbody>
					</table>
				</td>
			</tr>}
		</>
	);
};

export default RoomRow;

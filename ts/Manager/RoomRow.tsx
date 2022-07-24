import React, { useEffect, useState } from 'react';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { api, Recording, Room, Restriction, Access } from '../Common/Api';
import EditRoom from './EditRoom';
import RecordingRow from './RecordingRow';
import EditableValue from './EditableValue';
import { AccessOptions } from '../Common/Translation';

type Props = {
	room: Room;
	restriction?: Restriction;
	updateRoom: (room: Room) => Promise<void>;
	deleteRoom: (id: number) => void;
	cloneRoom: (room: Room) => void;
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
				<span className='sort_arrow'>{showRecordings ? '▾' : '▸'}</span> {recordings.length}
			</a>
		);
	}

	return <span>0</span>;
};

const RoomRow: React.FC<Props> = (props) => {
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
		return props.updateRoom({
			...props.room,
			[key]: value,
		});
	}

	function deleteRow(ev: MouseEvent) {
		ev.preventDefault();

		OC.dialogs.confirm(
			t('bbb', 'Are you sure you want to delete "{name}"? This operation cannot be undone.', { name: room.name }),
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
			t('bbb', 'Are you sure you want to delete the recording from "{startDate}"? This operation cannot be undone.', { startDate: (new Date(recording.startTime)).toLocaleString() }),
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

	function accessToIcon(access: string) {
		switch(access) {
		case Access.Public:
			return <span className="icon icon-visible icon-link" title={AccessOptions[access]} />;
		case Access.Password:
			return <span className="icon icon-visible icon-password" title={AccessOptions[access]} />;
		case Access.Internal:
			return <span className="icon icon-visible icon-group" title={AccessOptions[access]} />;
		case Access.InternalRestricted:
			return <span className="icon icon-visible icon-user" title={AccessOptions[access]} />;
		case Access.WaitingRoom:
			return <span className="icon icon-visible icon-timezone" title={AccessOptions[access]} />;
		}

		return <span></span>;
	}

	function edit(field: string, type: 'text' | 'number' = 'text', options?) {
		return <EditableValue field={field} value={room[field]} setValue={updateRoom} type={type} options={options} />;
	}

	function cloneRow() {
		props.cloneRoom({...props.room});
	}

	const avatarUrl = OC.generateUrl('/avatar/' + encodeURIComponent(room.userId) + '/' + 24, {
		user: room.userId,
		size: 24,
		requesttoken: OC.requestToken,
	});

	const maxParticipantsLimit = props.restriction?.maxParticipants || -1;
	const minParticipantsLimit = (props.restriction?.maxParticipants || -1) < 1 ? 0 : 1;

	return (
		<>
			<tr className={showRecordings ? 'selected-row' : ''}>
				<td className="start">
					<a href={api.getRoomUrl(room)} className={`button ${room.running ? 'success' : 'primary'}`} target="_blank" rel="noopener noreferrer" title={t('bbb', 'Open room')}>
						{room.running ? t('bbb', 'Join') : t('bbb', 'Start')}
					</a>
				</td>
				<td className="share icon-col">
					<CopyToClipboard text={api.getRoomUrl(room)}>
						<button className="action-item copy-to-clipboard" title={t('bbb', 'Copy to clipboard')}>
							<span className="icon icon-clippy icon-visible" ></span>
						</button>
					</CopyToClipboard>
				</td>
				<td className="store icon-col">
					<button className="action-item" onClick={() => storeRoom()} title={t('bbb', 'Save as file')}>
						<span className="icon icon-add-shortcut icon-visible"></span>
					</button>
				</td>
				<td className="name">
					{edit('name')}
				</td>
				<td className="bbb-shrink">
					{room.userId !== OC.currentUser && <img src={avatarUrl} alt="Avatar" className="bbb-avatar" />}
					{(room.userId === OC.currentUser && room.shared) && <span className="icon icon-shared icon-visible"/>}
				</td>
				<td>
					{accessToIcon(room.access)}
				</td>
				<td className="max-participants bbb-shrink">
					{edit('maxParticipants', 'number', {min: minParticipantsLimit, max: maxParticipantsLimit < 0 ? undefined : maxParticipantsLimit})}
				</td>
				<td className="record bbb-shrink">
					<input id={`bbb-record-${room.id}`} type="checkbox" className="checkbox" disabled={!props.restriction?.allowRecording} checked={room.record} onChange={(event) => updateRoom('record', event.target.checked)} />
					<label htmlFor={`bbb-record-${room.id}`}></label>
				</td>
				<td className="bbb-shrink"><RecordingsNumber recordings={recordings} showRecordings={showRecordings} setShowRecordings={setShowRecordings} /></td>
				<td className="edit icon-col">
					<EditRoom room={props.room} restriction={props.restriction} updateProperty={updateRoom} />
				</td>
				<td className="clone icon-col">
					<button className="action-item" onClick={cloneRow} title={t('bbb', 'Clone Room')}>
						<span className="icon icon-template-add icon-visible"></span>
					</button>
				</td>
				<td className="remove icon-col">
					<button className="action-item" onClick={deleteRow as any} title={t('bbb', 'Delete')}>
						<span className="icon icon-delete icon-visible"></span>
					</button>
				</td>
			</tr>
			{showRecordings && <tr className="recordings-row">
				<td colSpan={11}>
					<table>
						<tbody>
							{recordings?.sort((r1, r2) => r1.startTime - r2.startTime).map(recording => <RecordingRow key={recording.id} recording={recording} deleteRecording={deleteRecording} storeRecording={storeRecording} />)}
						</tbody>
					</table>
				</td>
			</tr>}
		</>
	);
};

export default RoomRow;

import React, { useEffect, useState } from 'react';
import './App.scss';
import RoomRow from './RoomRow';
import { SortArrow } from './SortArrow';
import { api, Room, Restriction, Access } from '../Common/Api';
import NewRoomForm from './NewRoomForm';

export type SortKey = 'name' | 'welcome' | 'maxParticipants' | 'record' | 'access';

enum SortOrder { DESC = -1, ASC = 1 }

function sortRooms(key: SortKey, orderBy: SortOrder) {
	return (a: Room, b: Room) => {
		switch (key) {
		case 'name':
		case 'welcome':
		case 'access':
			return a[key].localeCompare(b[key]) * orderBy;
		case 'maxParticipants':
			return (a.maxParticipants - b.maxParticipants) * orderBy;
		case 'record':
			if (a.record && !b.record) {
				return 1 * orderBy;
			}
			if (!a.record && b.record) {
				return -1 * orderBy;
			}

			return 0;
		}
	};
}

type Props = {

}

const App: React.FC<Props> = () => {
	const [isLoaded, setLoaded] = useState(false);
	const [error, setError] = useState<string>('');
	const [restriction, setRestriction] = useState<Restriction>();
	const [rooms, setRooms] = useState<Room[]>([]);
	const [orderBy, setOrderBy] = useState<SortKey>('name');
	const [sortOrder, setSortOrder] = useState(SortOrder.ASC);

	const rows = rooms.sort(sortRooms(orderBy, sortOrder)).map(room => <RoomRow room={room} restriction={restriction} key={room.id} updateRoom={updateRoom} deleteRoom={deleteRoom} cloneRoom={cloneRoom}/>);

	useEffect(() => {
		Promise.all([
			loadRestriction(),
			loadRooms(),
		]).catch(() => {
			setError(t('bbb', 'Server error'));
		}).then(() => {
			setLoaded(true);
		});
	}, []);

	function loadRestriction() {
		return api.getRestriction().then(restriction => {
			setRestriction(restriction);
		}).catch(err => {
			console.warn('Could not load restriction', err);

			throw err;
		});
	}

	function loadRooms() {
		return api.getRooms().then(rooms => {
			setRooms(rooms);
		}).catch((err) => {
			console.warn('Could not load rooms', err);

			throw err;
		});
	}

	function onOrderBy(key: SortKey) {
		if (orderBy === key) {
			setSortOrder(sortOrder === SortOrder.ASC ? SortOrder.DESC : SortOrder.ASC);
		}

		setOrderBy(key);
	}

	function addRoom(name: string) {
		if (!name) {
			return Promise.resolve();
		}

		let access = Access.Public;

		const disabledRoomTypes = restriction?.roomTypes || [];
		if (disabledRoomTypes.length > 0 && disabledRoomTypes.indexOf(access) > -1) {
			access = Object.values(Access).filter(a => disabledRoomTypes.indexOf(a) < 0)[0] as Access;
		}

		const maxParticipants = restriction?.maxParticipants || 0;

		return api.createRoom(name, access, maxParticipants).then(room => {
			setRooms(rooms.concat([room]));
		});
	}

	function updateRoom(room: Room) {
		return api.updateRoom(room).then(updatedRoom => {

			if (!rooms.find(room => room.id === updatedRoom.id)) {
				setRooms(rooms.concat([updatedRoom]));
			} else {
				setRooms(rooms.map(room => {
					if (room.id === updatedRoom.id) {
						return updatedRoom;
					}

					return room;
				}));
			}
		});
	}

	function deleteRoom(id: number) {
		api.deleteRoom(id).then(deletedRoom => {
			setRooms(rooms.filter(room => room.id !== deletedRoom.id));
		});
	}

	function cloneRoom(room: Room) {

		if (room.moderatorToken !== null) {
			room.moderatorToken = 'true';
		}

		return api.createRoom(room.name, room.access, room.maxParticipants).then(newRoom => {
			api.getRoomShares(room.id).then(shares => shares.forEach(share => {
				api.createRoomShare(newRoom.id, share.shareType, share.shareWith, share.permission);
			}));

			updateRoom({
				...room,
				uid: newRoom.uid,
				id: newRoom.id,
			});
		});
	}

	const maxRooms = restriction?.maxRooms || 0;
	const quota = maxRooms < 0 ? t('bbb', 'unlimited') : rooms.filter(room => room.userId === OC.currentUser).length + ' / ' + maxRooms;

	return (
		<div id="bbb-react-root"
			onClick={() => { /* @TODO hide edit inputs */ }}>
			<table>
				<thead>
					<tr>
						<th />
						<th />
						<th />
						<th onClick={() => onOrderBy('name')}>
							{t('bbb', 'Name')} <SortArrow name='name' value={orderBy} direction={sortOrder} />
						</th>
						<th />
						<th onClick={() => onOrderBy('access')} className="bbb-shrink">
							{t('bbb', 'Access')} <SortArrow name='access' value={orderBy} direction={sortOrder} />
						</th>
						<th onClick={() => onOrderBy('maxParticipants')} className="bbb-shrink">
							{t('bbb', 'Max')} <SortArrow name='maxParticipants' value={orderBy} direction={sortOrder} />
						</th>
						<th onClick={() => onOrderBy('record')} className="bbb-shrink">
							{t('bbb', 'Record')} <SortArrow name='record' value={orderBy} direction={sortOrder} />
						</th>
						<th>
							{t('bbb', 'Recordings')}
						</th>
						<th />
						<th />
						<th />
					</tr>
				</thead>
				<tbody>
					{rows}
				</tbody>
				<tfoot>
					<tr>
						<td colSpan={3}>
							{error && <><span className="icon icon-error icon-visible"></span> {error}</>}
							{!isLoaded && <span className="icon icon-loading-small icon-visible"></span>}
						</td>
						<td>
							{(maxRooms > rows.length || maxRooms < 0) ?
								<NewRoomForm addRoom={addRoom} /> :
								<p className="text-muted">{maxRooms === 0 ?
									t('bbb', 'You are not permitted to create a room.') :
									t('bbb', 'You exceeded the maximum number of rooms.')
								}</p>}
						</td>
						<td />
						<td colSpan={3}>
							<p className="text-muted">{t('bbb', 'Room quota:')} {quota}</p>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	);
};

export default App;

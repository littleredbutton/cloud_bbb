import axios from '@nextcloud/axios';

export enum ShareType { User, Group };

export enum Permission { Admin, Moderator, User };

export enum Access {
	Public = 'public',
	Password = 'password',
	WaitingRoom = 'waiting_room',
	Internal = 'internal',
	InternalRestricted = 'internal_restricted',
}

export interface Room {
	id: number;
	uid: string;
	name: string;
	welcome: string;
	maxParticipants: number;
	record: boolean;
	access: Access;
	password?: string;
}

export interface RoomShare {
	id: number;
	roomId: number;
	shareType: ShareType;
	shareWith: string;
	shareWithDisplayName?: string;
	permission: Permission;
}

export type Recording = {
	id: string;
	name: string;
	published: boolean;
	state: 'processing' | 'processed' | 'published' | 'unpublished' | 'deleted';
	startTime: number;
	participants: number;
	type: string;
	length: number;
	url: string;
	meta: any;
}

export interface ShareWith {
	users: {
		label: string;
		value: {
			shareType: ShareType;
			shareWith: string;
		};
	}[];
	groups: {
		label: string;
		value: {
			shareType: ShareType;
			shareWith: string;
		};
	}[];
}

class Api {
	public getUrl(endpoint: string): string {
		return OC.generateUrl(`apps/bbb/${endpoint}`);
	}

	public getRoomUrl(room: Room) {
		return window.location.origin + api.getUrl(`b/${room.uid}`);
	}

	public async getRooms(): Promise<Room[]> {
		const response = await axios.get(this.getUrl('rooms'));

		return response.data;
	}

	public async createRoom(name: string) {
		const response = await axios.post(this.getUrl('rooms'), {
			name,
			welcome: '',
			maxParticipants: 0,
			record: false,
		});

		return response.data;
	}

	public async updateRoom(room: Room) {
		const response = await axios.put(this.getUrl(`rooms/${room.id}`), room);

		return response.data;
	}

	public async deleteRoom(id: number) {
		const response = await axios.delete(this.getUrl(`rooms/${id}`));

		return response.data;
	}

	public async getRecordings(uid: string) {
		const response = await axios.get(this.getUrl(`server/${uid}/records`));

		return response.data;
	}

	public async deleteRecording(id: string) {
		const response = await axios.delete(this.getUrl(`server/record/${id}`));

		return response.data;
	}

	public async storeRecording(recording: Recording, path: string) {
		const startDate = new Date(recording.startTime);
		const filename = `${encodeURIComponent(recording.name + ' ' + startDate.toISOString())}.url`;
		const url = `/remote.php/dav/files/${OC.currentUser}${path}/${filename}`;

		await axios.put(url, `[InternetShortcut]\nURL=${recording.url}`);

		return filename;
	}

	public async storeRoom(room: Room, path: string) {
		const filename = `${encodeURIComponent(room.name)}.url`;
		const url = `/remote.php/dav/files/${OC.currentUser}${path}/${filename}`;

		await axios.put(url, `[InternetShortcut]\nURL=${this.getRoomUrl(room)}`);

		return filename;
	}

	public async checkServer(url: string, secret: string): Promise<'success' | 'invalid-url' | 'invalid:secret'> {
		const response = await axios.post(this.getUrl('server/check'), {
			url,
			secret,
		});

		return response.data;
	}

	public async getRoomShares(roomId: number): Promise<RoomShare[]> {
		const response = await axios.get(this.getUrl('roomShares'), {
			params: {
				id: roomId,
			},
		});

		return response.data;
	}

	public async createRoomShare(roomId: number, shareType: ShareType, shareWith: string, permission: Permission): Promise<RoomShare> {
		const response = await axios.post(this.getUrl('roomShares'), {
			roomId,
			shareType,
			shareWith,
			permission,
		});

		return response.data;
	}

	public async deleteRoomShare(id: number) {
		const response = await axios.delete(this.getUrl(`roomShares/${id}`));

		return response.data;
	}

	public async getRecommendedShareWith(): Promise<ShareWith> {
		const url = OC.linkToOCS('apps/files_sharing/api/v1', 1) + 'sharees_recommended';
		const response = await axios.get(url, {
			params: {
				itemType: 'room',
				format: 'json',
			},
		});

		return {
			users: response.data.ocs.data.exact.users,
			groups: response.data.ocs.data.exact.groups,
		};
	}

	public async searchShareWith(search = ''): Promise<ShareWith> {
		const url = OC.linkToOCS('apps/files_sharing/api/v1', 1) + 'sharees';
		const response = await axios.get(url, {
			params: {
				search,
				shareType: [OC.Share.SHARE_TYPE_USER, OC.Share.SHARE_TYPE_GROUP],
				itemType: 'room',
				format: 'json',
				lookup: false,
			},
		});

		return {
			users: response.data.ocs.data.users,
			groups: response.data.ocs.data.groups,
		};
	}
}

export const api = new Api();

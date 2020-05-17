import axios from '@nextcloud/axios';

export interface Room {
	id: number;
	uid: string;
	name: string;
	welcome: string;
	maxParticipants: number;
	record: boolean;
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

class Api {
	public getUrl(endpoint: string): string {
		return OC.generateUrl(`apps/bbb/${endpoint}`);
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
		const response = await axios.delete( this.getUrl(`rooms/${id}`));

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
		const url = `/remote.php/dav/files/${OC.currentUser}${path}/${encodeURIComponent(recording.name + ' ' + startDate.toISOString())}.url`;
		const response = await axios.put(url, `[InternetShortcut]\nURL=${recording.url}`);

		return response.data;
	}

	public async checkServer(url: string, secret: string): Promise<'success'|'invalid-url'|'invalid:secret'> {
		const response = await axios.post(this.getUrl('server/check'), {
			url,
			secret,
		});

		return response.data;
	}
}

export const api = new Api();

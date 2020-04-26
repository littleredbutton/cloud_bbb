import axios from '@nextcloud/axios';

export interface Room {
	id: number
	uid: string
	name: string
	welcome: string
	maxParticipants: number
	record: boolean
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
}

export const api = new Api();

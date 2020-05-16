import axios from '@nextcloud/axios';
import { generateOcsUrl, generateUrl } from '@nextcloud/router';
import { Room } from './Manager/Api';

declare const OCA: any;

class BigBlueButton {
	public async getRooms(): Promise<Room[]> {
		const response = await axios.get(OC.generateUrl('/apps/bbb/rooms'));

		return response.data;
	}
}

$(() => {

	if (!OCA?.Files?.fileActions) {
		return;
	}

	const mimeTypes = [
		'application/pdf',
		'application/vnd.oasis.opendocument.presentation',
		'application/vnd.oasis.opendocument.text',
		'application/vnd.oasis.opendocument.spreadsheet',
		'application/vnd.oasis.opendocument.graphics',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'application/msword',
		'application/vnd.ms-powerpoint',
		'application/vnd.ms-excel',
		'image/jpeg',
		'image/png',
		'text/plain',
		'text/rtf',
	];
	const bbb = new BigBlueButton();

	bbb.getRooms().then(rooms => {
		rooms.forEach(room => {
			mimeTypes.forEach(mime => registerFileAction(mime, room.id, room.uid, room.name));
		});
	});

	function registerFileAction(mime, id, uid, name) {
		OCA.Files.fileActions.registerAction({
			name: 'bbb-' + id,
			displayName: name,
			mime,
			permissions: OC.PERMISSION_SHARE,
			icon: OC.imagePath('bbb', 'app-dark.svg'),
			actionHandler: (fileName, context) => {
				share(context.fileInfoModel.getFullPath(), fileName, uid);
			},
		});
	}

	async function share(path: string, filename: string, roomUid) {
		const id = await createShare(path);
		const shareUrl = await configureShare(id);
		const joinUrl = generateUrl('/apps/bbb/b/{uid}?u={url}&filename={filename}', {
			uid: roomUid,
			url: shareUrl + '/download',
			filename,
		});

		window.open(joinUrl, '_blank', 'noopener,noreferrer');
	}

	async function createShare(path: string): Promise<number> {
		const url = generateOcsUrl('apps/files_sharing/api/v1', 2) + 'shares';

		const createResponse = await axios.post(url, {
			path,
			shareType: OC.Share.SHARE_TYPE_LINK,
		});

		const { meta, data } = createResponse.data.ocs;

		if (meta.statuscode !== 200) {
			throw new Error('Failed to create share');
		}

		return data.id;
	}

	async function configureShare(id: number): Promise<string> {
		const url = generateOcsUrl('apps/files_sharing/api/v1', 2) + 'shares/' + id;

		const tomorrow = new Date();
		tomorrow.setDate(new Date().getDate() + 1);

		const updateResponse = await axios.put(url, {
			expireDate: tomorrow.toISOString().split('T')[0],
			note: 'BigBlueButton',
		});

		const { meta, data } = updateResponse.data.ocs;

		if (meta.statuscode !== 200) {
			throw new Error('Failed to configure share');
		}

		return data.url;
	}
});
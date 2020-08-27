import axios from '@nextcloud/axios';
import { generateOcsUrl, generateUrl } from '@nextcloud/router';
import { Room } from './Common/Api';

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
				share(context.fileInfoModel.id, fileName, uid);
			},
		});
	}

	async function share(fileId: number, filename: string, roomUid) {
		const shareUrl = await createDirectShare(fileId);
		const joinUrl = generateUrl('/apps/bbb/b/{uid}?u={url}&filename={filename}', {
			uid: roomUid,
			url: shareUrl,
			filename,
		});

		window.open(joinUrl, '_blank', 'noopener,noreferrer');
	}

	async function createDirectShare(fileId: number): Promise<string> {
		const url = generateOcsUrl('apps/dav/api/v1', 1) + 'direct';
		const createResponse = await axios.post(url, {
			fileId,
		});

		return createResponse.data?.ocs?.data?.url;
	}
});

import axios from '@nextcloud/axios';
import { showSuccess, showWarning, showError } from '@nextcloud/dialogs';
import { generateOcsUrl, generateUrl } from '@nextcloud/router';

import { api } from './Common/Api';

async function createDirectShare(fileId: number): Promise<string> {
	const url = generateOcsUrl('apps/dav/api/v1/', undefined, {
		ocsVersion: 1,
		escape: true,
		noRewrite: true,
	}) + 'direct';
	const createResponse = await axios.post(url, {
		fileId,
	});

	return createResponse.data?.ocs?.data?.url;
}

function createRoomWithFile(shareUrl: string, filename: string, roomUid: string) {
	const joinUrl = generateUrl('/apps/bbb/b/{uid}?u={url}&filename={filename}', {
		uid: roomUid,
		url: shareUrl,
		filename,
	});

	window.open(joinUrl, '_blank', 'noopener,noreferrer');
}

function insertDocumentToRoom(shareUrl: string, filename: string, roomUid: string) {
	return api.insertDocument(roomUid, shareUrl, filename);
}

export async function sendFileToBBB(fileId: number, filename: string, roomUid: string) {
	const shareUrl = await createDirectShare(fileId);
	const isRunning = await api.isRunning(roomUid);

	if (isRunning) {
		try {
			const success = await insertDocumentToRoom(shareUrl, filename, roomUid);

			if (success) {
				showSuccess(t('bbb', 'The file "{filename}" was uploaded to your room.', { filename }));
			} else {
				showWarning(t('bbb', 'The file "{filename}" could not be uploaded to your room.', { filename }));
			}
		} catch {
			showError(t('bbb', 'The file "{filename}" could not be uploaded to your room. Maybe your BigBlueButton server does not support this action.', { filename }));
		}
	} else {
		createRoomWithFile(shareUrl, filename, roomUid);
	}
}

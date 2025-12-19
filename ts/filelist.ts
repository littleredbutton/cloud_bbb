import axios from '@nextcloud/axios';

import { generateOcsUrl, generateUrl } from '@nextcloud/router';
import { showSuccess, showWarning, showError } from '@nextcloud/dialogs';
// import * as Files from '@nextcloud/files';
import { FileAction, registerFileAction } from '@nextcloud/files';
import { api } from './Common/Api';
import Vue from 'vue';
import SendFileDialog from './views/SendFileDialog.vue';
import iconBBBInline from '../img/app-dark.svg?raw';

type NCNode = any;

const mimeTypes: readonly string[] = [
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

async function createRoomWithFile(shareUrl: string, filename: string, roomUid: string) {
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

/**
 * Create a DOM component to mount the dialog Vue component
 *
 * @param fileId number
 * @param filename string
 */
export function showSendFileDialog(fileId: number, filename: string	) {
	const mount = document.createElement('div');
	mount.id = 'bbb-widget-container';
	document.body.appendChild(mount);

	const vm = new Vue({
		el: '#bbb-widget-container',
		render: h => h(SendFileDialog, {
			props: {
				fileId,
				filename,
			},
			on: {
				// listen to 'close' event emitted by the dialog component, to clean up
				close: () => {
					vm.$destroy();
					mount.remove();
				},
			},
		}),
	});
}

/**
 * Register the file action "Send to BBB"
 */
registerFileAction( new FileAction({
	id: 'bbb-send-file',
	displayName: () => {
		return t('bbb', 'Send to BBB');
	},
	enabled: (nodes) => {
		// only files with the mime type allowed
		if (!Array.isArray(nodes) || nodes.length === 0) return false;

		return nodes.every((node): boolean | null => {
			const mime = node.mime;
			if (!mime) return false;
			// enable only for allowed mime types
			return mimeTypes.includes(mime);
		});
	},
	iconSvgInline: () => iconBBBInline,
	exec: async (node: NCNode) : Promise<boolean|null> => {
		showSendFileDialog(node.fileid, node.displayname);
		return null;
	},
	order: 20,
}));

import { registerFileAction } from '@nextcloud/files';
import type { INode, IFileAction } from '@nextcloud/files';
import iconBBBInline from '../img/app-dark.svg?raw';

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

/**
 * Register the file action "Send to BBB"
 */
const BBBFileAction: IFileAction = {
	id: 'bbb-send-file',
	displayName: () => {
		return t('bbb', 'Send to BBB');
	},
	enabled: ({ nodes }) => {
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
	exec: async ({ nodes }): Promise<boolean | null> => {
		const [ node ] : [INode | undefined] = nodes;
		if (!node || !node.fileid) {
			return null;
		}
		const { showSendFileDialog } = await import('./filelist-dialog');
		showSendFileDialog(node.fileid, node.displayname);
		return null;
	},
	order: 20,
};
registerFileAction(BBBFileAction);

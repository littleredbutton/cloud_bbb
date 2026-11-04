import Vue from 'vue';

import SendFileDialog from './views/SendFileDialog.vue';

/**
 * Create a DOM component to mount the dialog Vue component
 *
 * @param fileId number
 * @param filename string
 */
export function showSendFileDialog(fileId: number, filename: string) {
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

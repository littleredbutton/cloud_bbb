<template>
	<NcDialog
		dialog-classes="bbb-send-file-dialog"
		size="normal"
		:visible="opened"
		:name="title"
		@closing="onClose"
	>
		<NcLoadingIcon v-if="loading"
			:size="32"
			:name="t('bbb', 'Loading…')"
			appearance="dark"
		/>
		<div v-else class="send-file__content">
			<slot name="body">
				<p>
					{{ t('bbb', 'Please select the room in which you like to use the file "{filename}".', { filename }) }}
				</p>
				<p class="note">
					{{ t('bbb', 'Only rooms for which you are one of the administrators will be displayed.') }}
				</p>
				<div class="send-file__content__container">
					<table v-if="rooms.length > 0" class="send-file__content__table">
						<tr v-for="room in rooms" :key="room.id">
							<td>
								<NcButton
									:variant="room.running ? 'success' : 'primary'"
									@click="sendFileToRoom(room.uid)"
								>
									{{ room.running ? t('bbb', 'Send to') : t('bbb', 'Start with') }}
								</NcButton>
							</td>
							<td>{{ room.name }}</td>
						</tr>
					</table>
					<p v-else>
						{{ t('bbb', 'No rooms available!') }}
					</p>
				</div>
			</slot>
		</div>
		<template #actions>
			<button class="nc-btn nc-btn--tertiary" @click="onClose">
				{{ t('bbb','Close') }}
			</button>
		</template>
	</NcDialog>
</template>

<script>
import NcDialog from '@nextcloud/vue/components/NcDialog';
import NcButton from '@nextcloud/vue/components/NcButton';
import NcLoadingIcon from '@nextcloud/vue/components/NcLoadingIcon';
import { showError } from '@nextcloud/dialogs';
import { translate as t } from '@nextcloud/l10n';
import { api, Permission } from '../Common/Api';
import { sendFileToBBB } from '../filelist';

export default {
	name: 'SendFileDialog',
	components: {
		NcDialog,
		NcLoadingIcon,
		NcButton,
	},
	props: {
		filename: { type: String, required: true },
		fileId: { type: Number, required: true },
		title: { type: String, default: t('bbb', 'Send to BBB') },
	},
	data() {
		return {
			opened: false,
			loading: true,
			rooms: [],
		};
	},
	created() {

		api.getRooms().then((rooms) => {
			this.rooms = rooms.filter(room => room.permission === Permission.Admin);
			this.loading = false;
		});
	},
	methods: {
		t,

		onClose() {
			this.opened = false;
			this.$emit('close');
		},

		/*
			La confirmation déclenche un événement 'confirm'.
			Si la logique d'envoi est asynchrone côté parent, le parent peut gérer le flag `modelValue`/un loader.
			Ici on active un loader visuel court pour l'UX puis on ferme la dialog.
		*/
		onConfirm() {
			this.loading = true;
			try {
				this.$emit('confirm');
			} finally {
				// fermeture et reset loader (simulé court délai si nécessaire)
				this.loading = false;
				this.$emit('update:modelValue', false);
			}
		},
		sendFileToRoom(roomUid) {
			this.loading = true;
			sendFileToBBB(this.fileId, this.filename, roomUid).then(() => {
				this.loading = false;
				this.$emit('sent', roomUid);
				this.onClose();
			}).catch(() => {
				this.loading = false;
				showError(t('bbb', 'An error occurred while sending the file to the room.'));
			});
		},
	},
};
</script>

<style scoped lang="scss">
.send-file__title {
	margin: 0;
}
.send-file__info {
	margin: 0 0 0.5rem 0;
	word-break: break-all;
}
.nc-btn {
	min-width: 96px;
}
.send-file__content {
	margin-top: 1rem;

	&__table {
		width: 100%;
		margin-top: 1em;
		td {
			padding: 0.2rem;
		}
	}

	&__container {
		width: 80%;
		margin-right: auto;
		margin-left: auto;
		max-height: 400px;
		overflow-y: scroll auto;
	}
}

button.success {
	background-color: var(--color-success);
	border-color: var(--color-success-hover);
	color: var(--color-primary-text);

	&:hover {
		background-color: var(--color-success-hover);
		}
}
.bbb-send-file-dialog {
	min-width: 400px;
}
.send-file__content .note {
	margin-top: 0.5rem;
	font-size: 0.9rem;
	color: var(--color-secondary-text);
	font-style: italic;
}
</style>

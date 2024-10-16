import React from 'react';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { Recording } from '../Common/Api';

type Props = {
    recording: Recording;
	isAdmin : boolean;
    deleteRecording: (recording: Recording) => void;
    storeRecording: (recording: Recording) => void;
	publishRecording: (recording: Recording, publish: boolean) => void;
}

const RecordingRow: React.FC<Props> = ({recording, isAdmin, deleteRecording, storeRecording, publishRecording}) => {


	function checkPublished(recording: Recording, onChange: (value: boolean) => void) {
		return (
			<div>
				<input id={'bbb-record-state-' + recording.id}
					type="checkbox"
					className="checkbox"
					checked={recording.state === 'published'}
					onChange={(event) =>  onChange(event.target.checked)} />
				<label htmlFor={'bbb-record-state-' + recording.id}>{t('bbb', 'Published')}</label>
			</div>
		);
	}


	return (
		<tr key={recording.id}>
			<td className="start icon-col">
				<a href={recording.url} className="action-item" target="_blank" rel="noopener noreferrer" title={t('bbb', 'Open recording')}>
					<span className="icon icon-external icon-visible"></span>
				</a>
			</td>
			<td className="share icon-col">
				<CopyToClipboard text={recording.url} options={{format:'text/plain'}}>
					<button className="action-item copy-to-clipboard" title={t('bbb', 'Copy to clipboard')}>
						<span className="icon icon-clippy icon-visible" ></span>
					</button>
				</CopyToClipboard>
			</td>
			<td className="icon-col">
				<button className="action-item" onClick={() => storeRecording(recording)} title={t('bbb', 'Save as file')}>
					<span className="icon icon-add-shortcut icon-visible"></span>
				</button>
			</td>
			<td>
				{(new Date(recording.startTime)).toLocaleString()}
			</td>
			<td>
				{recording.length === 0 ? '< 1 min' : (recording.length + ' min')}
			</td>
			<td>
				{n('bbb', '%n participant', '%n participants', recording.participants)}
			</td>
			<td>
				{recording.type}
			</td>
			<td>
				{isAdmin && checkPublished(recording, (checked) => {
					publishRecording(recording, checked);
				})}
			</td>
			<td className="remove icon-col">
				{isAdmin &&
					<button className="action-item" onClick={() => deleteRecording(recording)} title={t('bbb', 'Delete')}>
						<span className="icon icon-delete icon-visible"></span>
					</button>
				}
			</td>
		</tr>
	);
};

export default RecordingRow;

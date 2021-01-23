import React from 'react';
import { CopyToClipboard } from 'react-copy-to-clipboard';
import { Recording } from '../Common/Api';

type Props = {
    recording: Recording;
    deleteRecording: (recording: Recording) => void;
    storeRecording: (recording: Recording) => void;
}

const RecordingRow: React.FC<Props> = ({recording, deleteRecording, storeRecording}) => {
	return (
		<tr key={recording.id}>
			<td className="start icon-col">
				<a href={recording.url} className="action-item" target="_blank" rel="noopener noreferrer" title={t('bbb', 'Open recording')}>
					<span className="icon icon-external icon-visible"></span>
				</a>
			</td>
			<td className="share icon-col">
				<CopyToClipboard text={recording.url}>
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
			<td className="remove icon-col">
				<button className="action-item" onClick={() => deleteRecording(recording)} title={t('bbb', 'Delete')}>
					<span className="icon icon-delete icon-visible"></span>
				</button>
			</td>
		</tr>
	);
};

export default RecordingRow;

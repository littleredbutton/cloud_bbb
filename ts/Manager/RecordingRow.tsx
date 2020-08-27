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
				<a href={recording.url} className="icon icon-external icon-visible" target="_blank" rel="noopener noreferrer"></a>
			</td>
			<td className="share icon-col">
				<CopyToClipboard text={recording.url}>
					<span className="icon icon-clippy icon-visible copy-to-clipboard" ></span>
				</CopyToClipboard>
			</td>
			<td className="icon-col">
				<a onClick={() => storeRecording(recording)} className="icon icon-add-shortcut icon-visible"></a>
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
				<a className="icon icon-delete icon-visible"
					onClick={() => deleteRecording(recording)}
					title={t('bbb', 'Delete')} />
			</td>
		</tr>
	);
};

export default RecordingRow;

import React, { useState } from 'react';

type Props = {
	addRoom: (name: string) => Promise<void>;
}

const NewRoomForm = (props: Props): JSX.Element => {
	const [name, setName] = useState<string>('');
	const [processing, setProcessing] = useState<boolean>(false);
	const [error, setError] = useState<string>('');

	function addRoom(ev: React.FormEvent) {
		ev.preventDefault();

		setProcessing(true);
		setError('');

		props.addRoom(name).then(() => {
			setName('');
		}).catch(err => {
			setError(err.toString());
		}).then(() => {
			setProcessing(false);
		});
	}

	return (
		<form action="#" onSubmit={addRoom}>
			<input
				className="newgroup-name"
				disabled={processing}
				value={name}
				placeholder={t('bbb', 'Room name')}
				onChange={(event) => { setName(event.target.value); }} />

			<input type="submit" disabled={processing} value={t('bbb', 'Create')} />

			{error && <p>{error}</p>}
		</form>
	);
};

export default NewRoomForm;

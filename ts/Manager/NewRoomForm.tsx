import React, {useState} from 'react';

type Props = {
    addRoom: (name: string) => void;
}

const NewRoomForm: React.FC<Props> = (props) => {
	const [name, setName] = useState('');

	function addRoom(ev: React.FormEvent) {
		ev.preventDefault();

		props.addRoom(name);

		setName('');
	}

	return (
		<form action="#" onSubmit={addRoom}>
			<input
				className="newgroup-name"
				value={name}
				placeholder="Room name"
				onChange={(event) => {setName(event.target.value)}} />

			<input type="submit" value="Create" />
		</form>
	)
}

export default NewRoomForm;
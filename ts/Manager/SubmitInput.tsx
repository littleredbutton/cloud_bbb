import React, {
	useState, useEffect, InputHTMLAttributes, SyntheticEvent,
} from 'react';

export interface SubmitInputProps extends InputHTMLAttributes<HTMLInputElement> {
	type?: 'text' | 'number';
	initialValue?: string;
	name: string;
	onSubmitValue: (value: string) => void;
	focus?: boolean;
}

export const SubmitInput = ({
	type = 'text',
	initialValue = '',
	name,
	onSubmitValue,
	focus,
	min,
	max,
	...rest
}: SubmitInputProps): JSX.Element => {
	const [value, setValue] = useState<string>(initialValue);

	useEffect(() => {
		setValue(initialValue ?? '');
	}, [initialValue]);

	const onSubmit = (e: SyntheticEvent) => {
		e.preventDefault();
		onSubmitValue(value);
	};

	return (
		<form onSubmit={onSubmit}>
			<input
				value={value}
				type={type}
				id={`bbb-${name}`}
				name={name}
				onChange={(ev) => setValue((ev.target as HTMLInputElement).value)}
				onBlur={() => onSubmitValue(value)}
				autoFocus={focus}
				min={min}
				max={max}
				{...rest}
			/>
		</form>
	);
};

import * as React from 'react';
import {
	Component, InputHTMLAttributes,
	SyntheticEvent,
} from 'react';

export interface SubmitInputProps extends InputHTMLAttributes<HTMLInputElement> {
	type?: 'text' | 'number';
	initialValue?: string;
	name: string;
	onSubmitValue: (value: string) => void;
	focus?: boolean;
}

export interface SubmitInputState {
	value: string;
}

export class SubmitInput extends Component<SubmitInputProps, SubmitInputState> {
	state: SubmitInputState = {
		value: '',
	};

	constructor(props: SubmitInputProps) {
		super(props);
		this.state.value = props.initialValue ?? '';
	}

	private onSubmit = (event: SyntheticEvent<any>) => {
		event.preventDefault();
		this.props.onSubmitValue(this.state.value);
	};

	public render(): JSX.Element {
		return <form onSubmit={this.onSubmit}>
			<input value={this.state.value}
				   type={this.props.type}
				   id={`bbb-${this.props.name}`}
				   name={this.props.name}
				   onChange={event => this.setState({value: event.currentTarget.value})}
				   onBlur={() => this.props.onSubmitValue(this.state.value)}
				   autoFocus={this.props.focus}
				   min={this.props.min}
				   max={this.props.max}
				   />
		</form>;
	}
}

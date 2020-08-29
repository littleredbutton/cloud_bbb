import { Access } from './Api';

interface EscapeOptions {
	escape?: boolean;
}

export function bt(string: string, vars?: { [key: string]: string }, count?: number, options?: EscapeOptions): string {
	return t('bbb', string, vars, count, options);
}

export const AccessOptions = {
	[Access.Public]: bt('Public'),
	[Access.Password]: bt('Internal + Password protection for guests'),
	[Access.WaitingRoom]: bt('Internal + Waiting room for guests'),
	[Access.Internal]: bt('Internal'),
	[Access.InternalRestricted]: bt('Internal restricted'),
};

import { Access, Permission } from './Api';
import parse from 'html-react-parser';
import DOMPurify from 'dompurify';

export const AccessOptions = {
	[Access.Public]: t('bbb', 'Public'),
	[Access.Password]: t('bbb', 'Internal + Password protection for guests'),
	[Access.WaitingRoom]: t('bbb', 'Internal + Waiting room for guests'),
	[Access.WaitingRoomAll]: t('bbb', 'Waiting room for all users'),
	[Access.Internal]: t('bbb', 'Internal'),
	[Access.InternalRestricted]: t('bbb', 'Internal restricted'),
};

export const PermissionsOptions = {
	[Permission.Admin]: t('bbb', 'admin'),
	[Permission.Moderator]: t('bbb', 'moderator'),
	[Permission.User]: t('bbb', 'user'),
};

export function html_sanitize_and_parse(str: string): string {
	return parse(DOMPurify.sanitize(str, { USE_PROFILES: { html: true } }));
}

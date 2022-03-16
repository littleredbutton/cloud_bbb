import { Access } from './Api';

export const AccessOptions = {
	[Access.Public]: t('bbb', 'Public'),
	[Access.Password]: t('bbb', 'Internal + Password protection for guests'),
	[Access.WaitingRoom]: t('bbb', 'Internal + Waiting room for guests'),
	[Access.WaitingRoomAll]: t('bbb', 'Waiting room for all users'),
	[Access.Internal]: t('bbb', 'Internal'),
	[Access.InternalRestricted]: t('bbb', 'Internal restricted'),
};

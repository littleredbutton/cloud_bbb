import React, { useEffect, useState } from 'react';
import '../Manager/App.scss';
import { api, Restriction, ShareType } from '../Common/Api';
import RestrictionRow from './RestrictionRow';
import ShareSelection from '../Common/ShareSelection';

const App = (): JSX.Element => {
	const [areRestrictionsLoaded, setRestrictionsLoaded] = useState(false);
	const [error, setError] = useState<string>('');
	const [restrictions, setRestrictions] = useState<Restriction[]>([]);

	const rows = restrictions.sort((a: Restriction, b: Restriction) => a.groupId.localeCompare(b.groupId)).map(restriction => <RestrictionRow key={restriction.id} restriction={restriction} updateRestriction={updateRestriction} deleteRestriction={deleteRestriction} />);

	useEffect(() => {
		api.getRestrictions().then(restrictions => {
			setRestrictions(restrictions);
		}).catch((err) => {
			console.warn('Could not load restrictions', err);

			setError(t('bbb', 'Server error'));
		}).then(() => {
			setRestrictionsLoaded(true);
		});
	}, []);

	function addRestriction(groupId: string) {
		return api.createRestriction(groupId).then(restriction => {
			setRestrictions([...restrictions, restriction]);
		});
	}

	function updateRestriction(restriction: Restriction) {
		return api.updateRestriction(restriction).then(updatedRestriction => {
			setRestrictions(restrictions.map((restriction: Restriction) => {
				if (restriction.id === updatedRestriction.id || restriction.groupId === updatedRestriction.groupId) {
					return updatedRestriction;
				}

				return restriction;
			}));
		});
	}

	function deleteRestriction(id: number) {
		api.deleteRestriction(id).then(deletedRestriction => {
			setRestrictions(restrictions.filter(restriction => restriction.id !== deletedRestriction.id));
		});
	}

	return (
		<div id="bbb-react-root">
			<table>
				<thead>
					<tr>
						<th>
							{t('bbb', 'Group name')}
						</th>
						<th>
							{t('bbb', 'Max. rooms')}
						</th>
						<th>
							{t('bbb', 'Access options')}
						</th>
						<th>
							{t('bbb', 'Max. participants')}
						</th>
						<th>
							{t('bbb', 'Recording')}
						</th>
						<th/>
					</tr>
				</thead>
				<tbody>
					{rows}
				</tbody>
				<tfoot>
					<tr>
						<td>
							{!areRestrictionsLoaded
								? <span className="icon icon-loading-small icon-visible"></span>
								: <ShareSelection
									placeholder={t('bbb', 'Group …')}
									selectShare={(share) => addRestriction(share.value.shareWith)}
									shareType={[ShareType.Group]}
									excluded={{groupIds: restrictions.map((restriction: Restriction) => restriction.groupId)}} /> }
							{error && <><span className="icon icon-error icon-visible"></span> {error}</>}
						</td>
						<td colSpan={4} />
					</tr>
				</tfoot>
			</table>

			<p className="text-muted">{t('bbb', 'Restrictions do not affect existing rooms. Minus one means the value is unlimited. The least restrictive option is chosen for every user if multiple restrictions apply.')}</p>
		</div>
	);
};

export default App;

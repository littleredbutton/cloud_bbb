import React, { useState, useEffect } from 'react';
import { api, ShareWith, ShareType, ShareWithOption } from '../Common/Api';
import './ShareSelection.scss';

type Props = {
	selectShare: (selection: ShareWithOption) => void;
	shareType?: ShareType[];
	excluded?: {
		groupIds?: string[];
		userIds?: string[];
		circleIds?: string[];
	};
	placeholder?: string;
}

const ShareSelection: React.FC<Props> = (props) => {
	const [search, setSearch] = useState<string>('');
	const [hasFocus, setFocus] = useState<boolean>(false);
	const [showSearchResults, setShowSearchResults] = useState<boolean>(false);
	const [recommendations, setRecommendations] = useState<ShareWith>();
	const [searchResults, setSearchResults] = useState<ShareWith>();

	const shareType = props.shareType || [ShareType.User, ShareType.Group];
	const excluded = {
		userIds: props.excluded?.userIds || [],
		groupIds: props.excluded?.groupIds || [],
		circleIds: props.excluded?.circleIds || [],
	};
	const placeholder = props.placeholder || t('bbb', 'Name, group, ...');

	useEffect(() => {
		setSearchResults(undefined);
		const searchQuery = search;

		if (!searchQuery) {
			return;
		}

		api.searchShareWith(searchQuery, shareType).then(result => {
			if (searchQuery === search) {
				setSearchResults(result);
			}
		});
	}, [search]);

	useEffect(() => {
		api.getRecommendedShareWith(shareType).then(result => setRecommendations(result));
	}, []);

	useEffect(() => {
		setTimeout(() => setShowSearchResults(hasFocus), 100);
	}, [hasFocus]);

	async function selectShare(share: ShareWithOption) {
		props.selectShare(share);

		setSearch('');
	}

	function renderSearchResults(options: ShareWith|undefined) {
		const results = options ? [
			...options.users.filter(user => !excluded.userIds.includes(user.value.shareWith)),
			...options.groups.filter(group => !excluded.groupIds.includes(group.value.shareWith)),
			...options.circles.filter(circle => !excluded.circleIds.includes(circle.value.shareWith)),
		] : [];

		const renderOption = (option: ShareWithOption) => {
			return (<li key={option.value.shareWith} className="suggestion" onClick={() => selectShare(option)}>
				{option.label}{option.value.shareType === ShareType.Group ? ` (${t('bbb', 'Group')})` : ''}
			</li>);
		};

		return (
			<ul className="bbb-selection">
				{!options ?
					<li><span className="icon icon-loading-small icon-visible"></span> {t('bbb', 'Searching')}</li> :
					(
						(results.length === 0 && search) ? <li>{t('bbb', 'No matches')}</li> : results.map(renderOption)
					)}
			</ul>
		);
	}

	return (
		<div className="bbb-selection-container">
			<input
				type="text"
				value={search}
				onChange={ev => setSearch(ev.currentTarget.value)}
				onFocus={() => setFocus(true)}
				onBlur={() => setFocus(false)}
				placeholder={placeholder} />
			{showSearchResults && renderSearchResults((search && searchResults) ? searchResults : ((recommendations && !search) ? recommendations : undefined))}
		</div>
	);
};

export default ShareSelection;

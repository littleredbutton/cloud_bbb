'use strict';

import App from './App';
import React from 'react';
import { render } from 'react-dom';

// Enable React devtools
window['React'] = React;

$(document).ready(() => {
	const root = document.getElementById('bbb-restrictions');
	if (root) {
		render(<App /> as any, root);
	}
});

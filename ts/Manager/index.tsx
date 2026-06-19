'use strict';

import App from './App';
import React from 'react';
import ReactDom from 'react-dom';
import $ from 'jquery';

// Enable React devtools
window['React'] = React;

$(document).ready(() => {
	const root = document.getElementById('bbb-root');
	if (root) {
		ReactDom.render( <App /> as any , root);
	}
});

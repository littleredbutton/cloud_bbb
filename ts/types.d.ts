declare module '*.svg?raw' {
	const content: string;
	export default content;
}

declare module '*.vue' {
	import { DefineComponent } from 'vue';
	const component: DefineComponent<{}, {}, any>;
	export default component;
}

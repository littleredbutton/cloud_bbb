import React from 'react';

type Props = {
    open: boolean;
    onClose?: () => void;
    title: string;
}

const Dialog: React.FC<Props> = ({open, title, children, onClose = () => undefined}) => {

	if (!open) {
		return <></>;
	}

	return (
		<>
			<div className="oc-dialog-dim" onClick={() => onClose()}> </div>
			<div className="oc-dialog bbb-dialog" tabIndex={-1} role="dialog" style={{display:'inline-block', position: 'fixed'}}>
				<h2 className="oc-dialog-title">{title}</h2>
				<a className="oc-dialog-close" onClick={ev => {ev.preventDefault(); onClose();}}></a>

				<div className="oc-dialog-content">
					{children}
				</div>
			</div>
		</>
	);
};

export default Dialog;
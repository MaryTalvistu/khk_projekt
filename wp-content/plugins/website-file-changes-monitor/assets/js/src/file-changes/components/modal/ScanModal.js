/**
 * Scan Modal.
 */
import React, { Component } from 'react';
import Modal from 'react-modal';
import fileEvents from '../helper/FileEvents';

export default class ScanModal extends Component {

	/**
	 * Constructor.
	 */
	constructor() {
		super();

		this.state = {
			modalIsOpen: true,
			scanning: false,
			backgroundScanInitiated: false,
			scanComplete: false,
			emailing: false,
			testMailSent: false,
			step: 'welcome',
		};

		this.stepIncrement      = this.stepIncrement.bind( this );
		this.openModal          = this.openModal.bind( this );
		this.closeModal         = this.closeModal.bind( this );
		this.startScan          = this.startScan.bind( this );
		this.sendTestEmail      = this.sendTestEmail.bind( this );
		this.titleRender        = this.titleRender.bind( this );
		this.mainMessageRender  = this.mainMessageRender.bind( this );
		this.mainButtonsRender  = this.mainButtonsRender.bind( this );
		this.emailMessageRender = this.emailMessageRender.bind( this );
		this.emailButtonsRender = this.emailButtonsRender.bind( this );
		this.emailSentMessage   = this.emailSentMessage.bind( this );

	}

	stepIncrement() {
		this.setState({ step: 'email' });
	}

	/**
	 * Open modal.
	 */
	openModal() {
		this.setState({ modalIsOpen: true });
	}

	/**
	 * Close modal.
	 */
	closeModal() {
		this.setState({ modalIsOpen: false });

		const requestUrl = `${wfcmFileChanges.scanModal.adminAjax}?action=wfcm_dismiss_instant_scan_modal&security=${wfcmFileChanges.security}`;
		let requestParams = { method: 'GET' };
		fetch( requestUrl, requestParams );
	}

	titleRender() {
		switch ( this.state.step ) {
			case 'email':
				return (
					<h2>{ ! this.state.emailSent ? wfcmFileChanges.scanModal.sendTestMail : wfcmFileChanges.scanModal.sendTestEmail }</h2>
				);
			case 'final':
			default:
				return (
					<h2>{ ! this.state.scanComplete ? wfcmFileChanges.scanModal.scanNow : wfcmFileChanges.scanModal.headingComplete }</h2>
				);
		}
	}

	mainMessageRender() {
		return (
			{__html:
				! this.state.backgroundScanInitiated ?
					wfcmFileChanges.scanModal.initialMsg :
					wfcmFileChanges.scanModal.bgScanMsg
			}
		);
	}

	emailMessageRender() {
		return ! this.state.testMailSent
			? { __html: wfcmFileChanges.scanModal.emailMsg }
			: this.emailSentMessage();
	}

	emailSentMessage() {

		return (
			{ __html:
				wfcmFileChanges.scanModal.emailSentLine1 + '<br><br>' + wfcmFileChanges.scanModal.emailSentLine2
			}
		);
	}

	mainButtonsRender() {
		return (
			<>
				<p>
					{
						! this.state.backgroundScanInitiated ?
							<input type="button" className="button-primary" value={! this.state.scanning ? wfcmFileChanges.scanModal.scanNow : wfcmFileChanges.scanModal.scanning} onClick={this.startScan} disabled={this.state.scanning} /> :
							<input type="button" className="button-primary" value={wfcmFileChanges.scanModal.ok} onClick={this.stepIncrement} />
					}
					&nbsp;
					{
						! this.state.scanComplete && ! this.state.backgroundScanInitiated ?
							<input type="button" className="button" value={wfcmFileChanges.scanModal.scanDismiss} onClick={this.stepIncrement} disabled={this.state.scanning} /> :
							null
					}
				</p>
				<p className="description" dangerouslySetInnerHTML=
					{
						! this.state.backgroundScanInitiated ?
							{ __html:
								wfcmFileChanges.scanModal.scheduleHelpTxt
							} :
							null
					}
				/>
			</>
		);
	}

	emailButtonsRender() {
		return (
			<p>
				{
					! this.state.testMailSent ?
						<input type="button" className="button button-primary" value={! this.state.emailing ? wfcmFileChanges.scanModal.sendTestMail : wfcmFileChanges.scanModal.emailSending} onClick={this.sendTestEmail} disabled={this.state.testMailSent} /> :
						(() => {
							return (
								<input type="button" className="button button-primary" value={wfcmFileChanges.scanModal.emailSent} disabled={this.state.testMailSent} />
							);
						})()
				}
				&nbsp;
				<input type="button" className="button" value={wfcmFileChanges.scanModal.exitButton} onClick={this.closeModal} />
			</p>
		)
	}


	/**
	 * Start the scan.
	 */
	async startScan( element ) {
		this.setState( () => ({
			scanning: true,
			backgroundScanInitiated: true
		}) );
		const targetElement = element.target;

		const scanRequest = fileEvents.getRestRequestObject( 'GET', wfcmFileChanges.monitor.start );
		let response      = await fetch( scanRequest );
		response          = await response.json();

		if ( response ) {
			this.setState( () => ({
				scanning: false,
				scanComplete: true
			}) );
		} else {
			targetElement.value = wfcmFileChanges.scanModal.scanFailed;
		}
	}

	/**
	 * Trigger a test email to send.
	 *
	 * @method sendTestEmail
	 */
	async sendTestEmail( element ) {
		this.setState({ emailing: true });
		const targetElement = element.target;

		const requestUrl    = `${wfcmFileChanges.scanModal.adminAjax}?action=wfcm_send_test_email&security=${wfcmFileChanges.security}`;
		const requestParams = { method: 'GET' };

		let response = await fetch( requestUrl, requestParams );
		response     = await response.json();

		if ( response.success ) {
			this.setState( () => ({
				emailing: false,
				testMailSent: true
			}) );
		} else {
			targetElement.value = wfcmFileChanges.scanModal.sendingFailed;
		}
	}

	/**
	 * Render the modal.
	 */
	render() {
		return (
			<React.Fragment>
				<Modal isOpen={this.state.modalIsOpen} onRequestClose={this.closeModal} style={modalStyles} contentLabel={wfcmFileChanges.scanModal.scanNow}>
					<div className="wfcm-modal-header">
						<span>
							<img src={wfcmFileChanges.scanModal.logoSrc} alt="WFCM" className="logo" />
							{ this.titleRender() }
						</span>
					</div>
					<div className="wfcm-modal-body">
						<p dangerouslySetInnerHTML=
						{
							this.state.step === 'email' ?
								this.emailMessageRender() :
								this.mainMessageRender()
						}
						/>
						{(() => {
							switch ( this.state.step ) {
								case 'email':
									return (
										this.emailButtonsRender()
									);
								default: return this.mainButtonsRender()
							}
						})()}
					</div>
				</Modal>
			</React.Fragment>
		);
	}
}

const modalStyles = {
	content: {
		top: '35%',
		left: '50%',
		right: 'auto',
		bottom: 'auto',
		marginRight: '-50%',
		transform: 'translate(-40%, -30%)',
		border: 'none',
		borderRadius: '0',
		padding: '0 16px 16px',
		width: '500px'
	}
};

Modal.defaultStyles.overlay.backgroundColor = 'rgba(0,0,0,0.5)';
Modal.setAppElement( '#wfcm-file-changes-view' );

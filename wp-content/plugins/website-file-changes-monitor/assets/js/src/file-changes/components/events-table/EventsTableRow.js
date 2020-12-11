/**
 * Events Table Rows.
 */
import React, { Component } from 'react';
import ContentModal from '../modal/ContentModal';
import ReactTooltip from 'react-tooltip';

export default class EventsTableRow extends Component {
	render() {
		const event = this.props.event;
		const contentType = event.contentType.toLowerCase();

		return (
			<tr>
				<td><input type="checkbox" value={event.id} checked={event.checked} onChange={this.props.selectEvent.bind( this, event.id )} /></td>
				<td>{event.path}</td>
				<td>{event.filename}</td>
				<td>
					<span className={`content-type ${contentType}`}>
						{
							'directory' === contentType && event.eventContext ?
								event.eventContext :
								event.contentType
						}
					</span>
				</td>
				<td>{event.dateTime}</td>
				<td><button className="wfcm-action-button"><span className="dashicons dashicons-yes-alt" onClick={this.props.markEventAsRead.bind( this, event.id )}></span></button></td>
				<td>
					{ 'file' === contentType ? <button className="wfcm-action-button wfcm-exclude-btn-file" data-tip={wfcmFileChanges.table.excludeFile} onClick={this.props.excludeEvent.bind( this, event.id, 'file' )}><span className="dashicons dashicons-admin-page"></span></button> : null }
					<button className="wfcm-action-button wfcm-exclude-btn-directory" data-tip={wfcmFileChanges.table.excludeDir} onClick={this.props.excludeEvent.bind( this, event.id, 'dir' )}><span className="dashicons dashicons-portfolio"></span></button>
					<ReactTooltip effect="solid" />
				</td>
				<td>
					{
						'directory' === contentType ?
							<ContentModal eventFiles={ event.content } /> :
							null
					}
				</td>
			</tr>
		);
	}
}

import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, Placeholder } from '@wordpress/components';
import { portfolio } from '@wordpress/icons';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { number } = attributes;

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'kyom')} initialOpen={true}>
					<RangeControl
						label={__('Number to display', 'kyom')}
						value={number}
						onChange={(value) => setAttributes({ number: value })}
						min={1}
						max={20}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Placeholder
					icon={portfolio}
					label={__('Recent Projects', 'kyom')}
					instructions={__('Displays recent portfolio items in a slideshow. Configure the number of items to show in the sidebar.', 'kyom')}
				>
					<p>{__('Number to display:', 'kyom')} {number}</p>
					<p style={{ fontSize: '0.9em', color: '#757575' }}>
						{__('Note: This block requires Jetpack Portfolio to be enabled.', 'kyom')}
					</p>
				</Placeholder>
			</div>
		</>
	);
}

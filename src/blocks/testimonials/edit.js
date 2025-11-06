import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, Placeholder } from '@wordpress/components';
import { starFilled } from '@wordpress/icons';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { number, order } = attributes;

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'kyom')} initialOpen={true}>
					<RangeControl
						label={__('Number of Display', 'kyom')}
						value={number === -1 ? 20 : number}
						onChange={(value) => setAttributes({ number: value === 20 ? -1 : value })}
						min={1}
						max={20}
						help={__('-1 means all testimonials', 'kyom')}
					/>
					<SelectControl
						label={__('Order', 'kyom')}
						value={order}
						options={[
							{ label: __('Random', 'kyom'), value: 'random' },
							{ label: __('From Newest', 'kyom'), value: 'desc' },
							{ label: __('Post Order', 'kyom'), value: 'post_order' },
						]}
						onChange={(value) => setAttributes({ order: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Placeholder
					icon={starFilled}
					label={__('Testimonials', 'kyom')}
					instructions={__('Displays testimonials in a slider. Configure settings in the sidebar.', 'kyom')}
				>
					<p>{__('Number:', 'kyom')} {number === -1 ? __('All', 'kyom') : number}</p>
					<p>{__('Order:', 'kyom')} {order}</p>
					<p style={{ fontSize: '0.9em', color: '#757575' }}>
						{__('Note: This block requires Jetpack Testimonials to be enabled.', 'kyom')}
					</p>
				</Placeholder>
			</div>
		</>
	);
}

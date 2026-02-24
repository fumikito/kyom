import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Placeholder } from '@wordpress/components';
import { rss } from '@wordpress/icons';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { url } = attributes;

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'kyom')} initialOpen={true}>
					<TextControl
						label={__('Feed URL', 'kyom')}
						value={url}
						onChange={(value) => setAttributes({ url: value })}
						type="url"
						help={__('Enter the RSS feed URL to display', 'kyom')}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Placeholder
					icon={rss}
					label={__('RSS Sliders', 'kyom')}
					instructions={__('Displays RSS feed items in a slider. Enter the feed URL in the sidebar.', 'kyom')}
				>
					{url && <p>{__('Feed URL:', 'kyom')} {url}</p>}
				</Placeholder>
			</div>
		</>
	);
}

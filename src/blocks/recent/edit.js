import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, Placeholder } from '@wordpress/components';
import { postList } from '@wordpress/icons';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { title, category } = attributes;

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'kyom')} initialOpen={true}>
					<TextControl
						label={__('Title', 'kyom')}
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						help={__('Leave empty for default title "Recent Posts"', 'kyom')}
					/>
					<TextControl
						label={__('Category', 'kyom')}
						value={category}
						onChange={(value) => setAttributes({ category: value })}
						help={__('Enter category ID to filter posts', 'kyom')}
						type="number"
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<Placeholder
					icon={postList}
					label={title || __('Recent Posts', 'kyom')}
					instructions={__('Displays recent posts in a grid layout. Configure settings in the sidebar.', 'kyom')}
				>
					{category && <p>{__('Category ID:', 'kyom')} {category}</p>}
				</Placeholder>
			</div>
		</>
	);
}

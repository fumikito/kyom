import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, Placeholder } from '@wordpress/components';
import { share } from '@wordpress/icons';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { showBio, showArchive } = attributes;

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'kyom' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show biography', 'kyom' ) }
						checked={ showBio }
						onChange={ ( value ) => setAttributes( { showBio: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show link to all posts', 'kyom' ) }
						checked={ showArchive }
						onChange={ ( value ) =>
							setAttributes( { showArchive: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<Placeholder
					icon={ share }
					label={ __( 'Follow', 'kyom' ) }
					instructions={ __(
						'Shows the author profile and follow links. URLs come from the profile contact info, wording from the Customizer.',
						'kyom'
					) }
				/>
			</div>
		</>
	);
}

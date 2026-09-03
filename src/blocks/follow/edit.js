import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	CheckboxControl,
	Placeholder,
} from '@wordpress/components';
import { share } from '@wordpress/icons';
import './editor.scss';

/**
 * 大きく見せる媒体。functions/follow.php の kyom_follow_featured_keys() と対応する。
 */
const CHANNELS = [
	{ key: 'twitter', label: 'X (Twitter)' },
	{ key: 'youtube', label: 'YouTube' },
	{ key: 'mail', label: __( 'Newsletter', 'kyom' ) },
];

export default function Edit( { attributes, setAttributes } ) {
	const { title, lead, keys } = attributes;

	const blockProps = useBlockProps();

	// keys が空なら「全部表示」の意味。チェックボックスは全部オンに見せる。
	const isChecked = ( key ) => keys.length === 0 || keys.includes( key );

	const toggleChannel = ( key, checked ) => {
		const current = keys.length === 0 ? CHANNELS.map( ( c ) => c.key ) : keys;
		const next = checked
			? [ ...current, key ]
			: current.filter( ( k ) => k !== key );
		// 全部オンなら空配列に戻し、「全部表示」の状態を保つ。
		setAttributes( {
			keys: next.length === CHANNELS.length ? [] : next,
		} );
	};

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'kyom' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Title', 'kyom' ) }
						value={ title }
						onChange={ ( value ) => setAttributes( { title: value } ) }
						help={ __(
							'Leave empty to use the value set in the Customizer.',
							'kyom'
						) }
					/>
					<TextareaControl
						label={ __( 'Lead', 'kyom' ) }
						value={ lead }
						onChange={ ( value ) => setAttributes( { lead: value } ) }
						help={ __(
							'Leave empty to use the value set in the Customizer.',
							'kyom'
						) }
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Channels', 'kyom' ) }
					initialOpen={ false }
				>
					{ CHANNELS.map( ( channel ) => (
						<CheckboxControl
							key={ channel.key }
							label={ channel.label }
							checked={ isChecked( channel.key ) }
							onChange={ ( checked ) =>
								toggleChannel( channel.key, checked )
							}
						/>
					) ) }
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<Placeholder
					icon={ share }
					label={ __( 'Follow', 'kyom' ) }
					instructions={ __(
						'Shows links to follow you. URLs come from your profile contact info, wording from the Customizer.',
						'kyom'
					) }
				>
					<p>
						{ __( 'Channels:', 'kyom' ) }{ ' ' }
						{ CHANNELS.filter( ( c ) => isChecked( c.key ) )
							.map( ( c ) => c.label )
							.join( ' / ' ) }
					</p>
				</Placeholder>
			</div>
		</>
	);
}

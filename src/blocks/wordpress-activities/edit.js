import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import { ColorPicker } from '@wordpress/components';
import { wordpress } from '@wordpress/icons';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { userName, userMail, backgroundColor, content } = attributes;

	const blockProps = useBlockProps();

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Settings', 'kyom')} initialOpen={true}>
					<TextControl
						label={__('WordPress User Name', 'kyom')}
						value={userName}
						onChange={(value) => setAttributes({ userName: value })}
						help={__('Enter WordPress.org username', 'kyom')}
					/>
					<TextControl
						label={__('WordPress User Email', 'kyom')}
						value={userMail}
						onChange={(value) => setAttributes({ userMail: value })}
						type="email"
						help={__('Enter email for Gravatar', 'kyom')}
					/>
					<div style={{ marginBottom: '16px' }}>
						<label style={{ display: 'block', marginBottom: '8px', fontWeight: 600 }}>
							{__('Background Color', 'kyom')}
						</label>
						<ColorPicker
							color={backgroundColor}
							onChangeComplete={(value) => setAttributes({ backgroundColor: value.hex })}
						/>
					</div>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div style={{
					backgroundColor: backgroundColor || '#f5f5f5',
					padding: '20px',
					borderRadius: '4px',
				}}>
					<div style={{ textAlign: 'center', marginBottom: '16px' }}>
						{wordpress}
						<h3 style={{ margin: '10px 0' }}>{__('WordPress Activity', 'kyom')}</h3>
						{userName && <p><strong>{__('User:', 'kyom')}</strong> {userName}</p>}
					</div>
					<RichText
						tagName="div"
						value={content}
						onChange={(value) => setAttributes({ content: value })}
						placeholder={__('Enter description...', 'kyom')}
						style={{ textAlign: 'center', marginTop: '16px' }}
					/>
				</div>
			</div>
		</>
	);
}

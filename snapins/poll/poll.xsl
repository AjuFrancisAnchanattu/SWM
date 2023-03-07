<?xml version="1.0"?>

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<xsl:template match="poll">
		
	
		<table cellspacing="2" width="260">
			<tr>
				<td colspan="2"><b>{TRANSLATE:poll_question_1}</b></td>
			</tr>
			<tr>
				<td width="80">{TRANSLATE:poll_like_it}</td>
				<td width="180">
					<table width="{widthValueLike}">
						<tr>
							<td style="background-color: #990000;" height="15px"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="80">{TRANSLATE:dont_like_it}</td>
				<td width="180">
					<table width="{widthValueDontLike}">
						<tr>
							<td style="background-color: #333333;" height="15px"></td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td width="80">{TRANSLATE:no_comment}</td>
				<td width="180">
					<table width="{widthValueNA}">
						<tr>
							<td style="background-color: #CCCCCC;" height="15px"></td>
						</tr>
					</table>
				</td>
			</tr>
		</table>

	</xsl:template>
	
</xsl:stylesheet>
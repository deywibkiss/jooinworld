<?php $email = sprintf( 'Hola %s, Gracias por registrarte en %s!. Ahora puede acceder a %s usando el nombre de usuario y la contrase&ntilde;a que registr&oacute;.', 'Brayan', 'Jooinworld', '<a href="#">http://www.jooinworld.com/</a>' ) ?>
<table style="width: 560px; border: 2px solid #4E78C2; font-family: Arial;">
	<tbody>
		<tr style="background-color: #4E78C2; color: #FFF;">
			<td style="padding: 10px; text-align: center; font-weight: bold; text-transform: uppercase;">
				<img src="http://localhost/jooinworld/images/logo.png"/>
			</td>
		</tr>
		<tr>
			<td><img src="http://localhost/jooinworld/images/employments.png"/></td>
		</tr>
		<tr>
			<td style="padding: 10px; text-align: center; color:#000;">
				<?php echo $email; ?>
			</td>
		</tr>

		<tr style="background-color: #4E78C2; color: #FFF;">
			<td style="padding: 10px; text-align: center; font-size:11px;">
				Jooinworld 2013 - Todos los derechos reservados
			</td>
		</tr>
	</tbody>
</table>
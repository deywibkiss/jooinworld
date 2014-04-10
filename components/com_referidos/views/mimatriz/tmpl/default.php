<?php 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$toolbar = CToolbarLibrary::getInstance();
$document = JFactory::getDocument();

echo '<div id="community-wrap">'.$toolbar->getHTML().'</div>';
?>
<div class="content-referidos">
<?php 
if( $this->tieneplan )
	echo '<div class="content-barra"><h4>Mi Matriz</h4>' .ToolBarReferidos::render( 'Mi Matriz' ). '</div>';
?>
    <form name="form-matriz" id="form-matriz" action="index.php?option=com_referidos&task=plan.mimatriz" method="post" class="referidos-form">
        <select name="plan" id="plan" data-placeholder="Seleccione plan" class="list-alt" onchange="this.form.submit();">
        	<option value="">Seleccione Plan</option>
        	<?php 
				$planes = $this->planes();
				foreach( $planes as $key=>$plan):
			?>
            <option value="<?php echo $plan->id_plan;?>" <?php if( $this->plan->id_plan == $plan->id_plan ){ echo 'selected="selected"';} ?>><?php echo $plan->nom_plan;?></option>
            <?php endforeach;?>
        </select>
        <input type="hidden" name="option" value="com_referidos" />
        <input type="hidden" name="task" value="plan.mimatriz" />
    </form>
    <div class="datagrid datagrid-head">
        <table>
            <thead>
                <tr>
                    <th class="datagrid-center" style="width:10%"><span>Nivel</span></th>
                    <th class="datagrid-center" style="width:30%"><span>Personas</span></th>
                    <th class="datagrid-center" style="width:30%"><span>Comisión por Persona</span></th>
                    <th class="datagrid-center" style="width:30%"><span>Comisión por Nivel</span></th>
                <tr>    
            </thead>
        </table>
    </div>
    <div class="datagrid">
        <table>
            <tbody>
            	<?php 
					//var_dump( $this->plan );
					$niveles = ( int ) $this->plan->niveles;
					$personas = $this->plan->personas_nivel;
					$comi_nivel_total = array();
					
					//var_dump( $plan );
					
					$per_pow = 0;
					
				for( $i=0; $i < $niveles ; $i++):?>          
                <tr <?php if( strtotime( date( $this->plan->fecha_vence ) ) < strtotime( 'now' ) ){ echo 'style="background-image:url(\'marcadeagua_vencido.png\')"';}?>>
                    <td class="datagrid-pos datagrid-center" style="width:10%"><?php echo $i+1;?></td>
                    <td class="datagrid-center" style="width:30%"><?php if( $per_pow <= 0 ){ $per_pow = $personas; }else{ $per_pow = pow( $personas , $i+1 ); } echo $per_pow;?></td>
                    <td class="datagrid-center" style="width:30%"><?php echo '$ ' . number_format( $this->plan->comision_x_persona , 2 , ',' ,'.');?></td>
                    <td class="datagrid-center" style="width:30%"><?php 
						$comi_nivel_total[] = ( floatval( $per_pow )  * floatval( $this->plan->comision_x_persona ) ); 
						echo '$ ' . number_format( ( floatval( $per_pow )  * floatval( $this->plan->comision_x_persona ) ) , 2 , ',' , '.');
					?></td>
                </tr>
                <?php endfor;?>
            </tbody>
            <tfoot>
            	<tr style="background-color:#EBEBEB">
                	<td style="width:10%">&nbsp;</td>
                    <td style="width:30%">&nbsp;</td>
                    <td style="width:30%">&nbsp;</td>
                    <td style="font-weight:bold; width:30%"><?php echo '$ '.number_format( array_sum( $comi_nivel_total ), 2 , ',' , '.');?></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>    
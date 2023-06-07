<?php if (!isset($this->attribs)) $this->attribs = array(); ?>
<?php
    $attribs = [];
    foreach ($this->attribs as $key => $value) {
        unset($value['class']);
        $attribs[$key] = $this->htmlAttribs( $value );
    }
?>
<?php $px = ($this->type === 'px') ? true : false; ?>
<?php if (count($this->columns) === 3):?>
    <v-layout wrap fill-height>
        <v-flex xs12 md4 <?php echo isset($attribs[0]) ? $attribs[0] : '' ?>>
            <?php echo $this->columns[0]; ?>
        </v-flex>
        <v-flex xs12 md4 <?php echo isset($attribs[1]) ? $attribs[1] : '' ?>>
            <?php echo $this->columns[1]; ?>
        </v-flex>
        <v-flex xs12 md4 <?php echo isset($attribs[2]) ? $attribs[2] : '' ?>>
            <?php echo $this->columns[2]; ?>
        </v-flex>
    </v-layout>
<?php elseif (count($this->columns) === 2 || empty($this->columns)):?>
    <v-layout wrap fill-height>
        <v-flex xs12 md6 <?php echo isset($attribs[0]) ? $attribs[0] : '' ?>>
            <?php echo $this->columns[0]; ?>
        </v-flex>
        <v-flex xs12 md6 <?php echo isset($attribs[1]) ? $attribs[1] : '' ?>>
            <?php echo $this->columns[1]; ?>
        </v-flex>
    </v-layout>
<?php else:?>
    <v-layout>
        <v-flex>
            <?php
            foreach($this->columns as $value) {
                echo $value;
            }
            ?>
        </v-flex>
    </v-layout>
<?php endif;?>


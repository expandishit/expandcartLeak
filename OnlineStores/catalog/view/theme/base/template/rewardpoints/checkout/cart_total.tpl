<table id="total">
    <?php foreach ($totals as $total) { ?>
    <tr>
        <td class="right"><b><?php echo $total['title']; ?>:</b></td>
        <td class="right"><?php echo $total['text']; ?></td>
    </tr>
    <?php } ?>
</table>

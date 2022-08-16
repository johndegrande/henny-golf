<style>
    .field-order-block {
        border: 1px solid #CCCCCC;
    }

    .field-order-block > div {
        background: #EEEEEE;
        border-bottom: 1px solid #CCCCCC;

        color: #00BC69;
        font-weight: bold;
    }

    .field-order-block > ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .field-order-block > ul > li {
        position: relative;
        display: block;
        padding-left: 20px;

        background: #F9F9F9;
        border-bottom: 1px solid #CCCCCC;
    }

    .field-order-block > ul > li .handle {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;

        display: block;
        padding: 8px 4px 0 8px;
    }

    .field-order-block > ul > li:last-child {
        border-bottom: none;
    }

    .field-order-block > ul > li.active {
        background: #E9F7FD;
        border-bottom: 1px solid #8FD4F3;
    }

    .field-order-block > ul > li.active:last-child {
        border-bottom: none;
    }

    .field-order-block > ul > li > label, .field-order-block > div {
        display: block;
        padding: 5px 10px 4px;
    }
</style>

<div class="field-order-block">
    <div>
        <label>
            <input type="checkbox" />
            <?= lang('Check All') ?>
        </label>
    </div>
    <ul>
        <?php foreach ($available_fields as $field_id => $field): ?>
            <?php $checked = in_array($field_id, $selected, false) ?>
            <li class="<?= $checked ? 'active' : '' ?>">
                <span class="handle icon-reorder"></span>
                <label>
                    <input type="hidden"
                           name="<?= $field_name ?>[order][]"
                           value="<?= $field_id ?>"
                    />
                    <input type="checkbox"
                           name="<?= $field_name ?>[values][]"
                           value="<?= $field_id ?>"
                           <?= $checked ? 'checked' : '' ?>
                    />
                    <?= $field ?>
                </label>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
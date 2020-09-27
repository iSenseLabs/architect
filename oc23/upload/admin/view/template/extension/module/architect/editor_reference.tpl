<h2>Quick Reference <small>Complete documentation, please refer to the <a href="https://github.com/iSenseLabs/architect/wiki" target="_blank">Wiki</a></small></h2>
<hr class="hr">

<h3 class="legend">On Save</h3>
<p>Each editor will be saved to respected file and database. This approach allow the module to work per OpenCart system workflow.</p>

<table class="table table-striped mt-10">
    <thead>
        <tr>
            <td class="col-xs-2 col-sm-3">Editor</td>
            <td>Save Destination</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Controller</td>
            <td>catalog/controller/extension/architect/<code><?php echo $docs['identifier']; ?></code>.php</td>
        </tr>
        <tr>
            <td>Model</td>
            <td>catalog/model/extension/architect/<code><?php echo $docs['identifier']; ?></code>.php</td>
        </tr>
        <tr>
            <td>Template</td>
            <td>catalog/view/theme/default/template/extension/architect/<code><?php echo $docs['identifier']; ?></code>.tpl</td>
        </tr>
        <tr>
            <td>Modification</td>
            <td>Database table <code>modification</code> with "code" identifier <code>architect_<?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>Event</td>
            <td>
                Database table <code>event</code> with "code" identifier <code>architect_<?php echo $docs['identifier']; ?></code><br>
                catalog/controller/extension/architect/event/<code><?php echo $docs['identifier']; ?></code>.php
            </td>
        </tr>
        <tr>
            <td>Admin Controller</td>
            <td>admin/controller/extension/architect/<code><?php echo $docs['identifier']; ?></code>.php</td>
        </tr>
    </tbody>
</table>


<h3 class="legend">Codetags</h3>
<p>Custom tags that replaced when save sub-module and it's available to all editors.</p>

<table class="table table-striped mt-10">
    <thead>
        <tr>
            <td class="col-xs-2 col-sm-3">Tags</td>
            <td>Replacement</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{module_id}</td>
            <td><b><?php echo $docs['module_id']; ?></b></td>
        </tr>
        <tr>
            <td>{identifier}</td>
            <td><b><?php echo $docs['identifier']; ?></b></td>
        </tr>
        <tr>
            <td>{author}</td>
            <td><?php echo $docs['author']; ?></td>
        </tr>
        <tr>
            <td>{controller_class}</td>
            <td>ControllerExtensionArchitect<code><?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>{model_class}</td>
            <td>ModelExtensionArchitect<code><?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>{model_path}</td>
            <td>extension/architect/<code><?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>{model_call}</td>
            <td>extension_architect_<code><?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>{template_path}</td>
            <td>extension/architect/<code><?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>{ocmod_name}</td>
            <td>Architect #<code><?php echo $docs['module_id']; ?></code> - Sub-module name</td>
        </tr>
        <tr>
            <td>{ocmod_code}</td>
            <td><code>architect_<?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>{event_class}</td>
            <td>ControllerExtensionArchitectEvent<code><?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>{event_path}</td>
            <td>extension/architect/event/<code><?php echo $docs['identifier']; ?></code></td>
        </tr>
        <tr>
            <td>{admin_controller_class}</td>
            <td>ControllerExtensionArchitect<code><?php echo $docs['identifier']; ?></code></td>
        </tr>
    </tbody>
</table>

<p><b>Notes:</b></p>
<ul>
    <li>The <code>{identifier}</code> replacement above is the real identifier for current sub-module.</li>
    <li>For consistency, Modification and Event <code>code</code> always replaced with a prefixed sub-module identifier.</li>
    <li>Admin controller have special method <code>onSave()</code> and <code>onDelete</code> as a hook for sub-module action.</li>
    <li>Tab Options: when all options evaluated to true then sub-module entrance <code>catalog controller::index()</code> will be executed. This options not affecting Model, Template, Modification, Event or Admin Controller.</li>
</ul>

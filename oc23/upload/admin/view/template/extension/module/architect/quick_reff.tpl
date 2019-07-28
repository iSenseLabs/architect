<h2 class=" text-center">Quick Refference</h2>
<hr class="hr">

<p>For complete documentation, please refer to the project <a href="https://github.com/iSenseLabs/architect/wiki" target="_blank">Wiki</a>.</p>

<h3 class="legend">On Save</h3>
<p>Each editor will be saved to respected file and database. This approach allow the module to work without any magic involvement and work per OpenCart system workflow.</p>

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
            <td>catalog/controller/extension/architect/<code>arc101</code>.php</td>
        </tr>
        <tr>
            <td>Model</td>
            <td>catalog/model/extension/architect/<code>arc101</code>.php</td>
        </tr>
        <tr>
            <td>Template</td>
            <td>catalog/model/extension/architect/<code>arc101</code>.php</td>
        </tr>
        <tr>
            <td>Modification</td>
            <td>Saved to database</td>
        </tr>
        <tr>
            <td>Event</td>
            <td>
                Saved to database<br>
                catalog/controller/extension/architect/event/<code>arc101</code>.php
            </td>
        </tr>
    </tbody>
</table>


<h3 class="legend">Codetags</h3>
<p>Custom code available to all editors, which is replaced when save sub-module.</p>

<table class="table table-striped mt-10">
    <thead>
        <tr>
            <td class="col-xs-2 col-sm-3">Tags</td>
            <td>Replacement Sample</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{module_id}</td>
            <td><b>21</b></td>
        </tr>
        <tr>
            <td>{identifier}</td>
            <td><b>arc101</b> <i class="small">* unique per sub-module</i></td>
        </tr>
        <tr>
            <td>{author}</td>
            <td>johndoe</td>
        </tr>
        <tr>
            <td>{controller_class}</td>
            <td>ControllerExtensionArchitect<code>arc101</code></td>
        </tr>
        <tr>
            <td>{model_class}</td>
            <td>ModelExtensionArchitect<code>arc101</code></td>
        </tr>
        <tr>
            <td>{model_path}</td>
            <td>extension/architect/<code>arc101</code></td>
        </tr>
        <tr>
            <td>{model_call}</td>
            <td>extension_architect_<code>arc101</code></td>
        </tr>
        <tr>
            <td>{template_path}</td>
            <td>extension/architect/<code>arc101</code></td>
        </tr>
        <tr>
            <td>{ocmod_name}</td>
            <td>Architect #<code>21</code> - Sub-module name</td>
        </tr>
        <tr>
            <td>{ocmod_code}</td>
            <td><code>arc101</code></td>
        </tr>
        <tr>
            <td>{event_class}</td>
            <td>ControllerExtensionArchitectEvent<code>arc101</code></td>
        </tr>
        <tr>
            <td>{event_path}</td>
            <td>extension/architect/event/<code>arc101</code></td>
        </tr>
    </tbody>
</table>

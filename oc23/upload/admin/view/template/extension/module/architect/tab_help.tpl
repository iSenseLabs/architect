<div class="row">
    <div class="col-md-5">
        <h1><?php echo $architect['title']; ?></h1>
        <p>Architect is an OpenCart module for rapid extension development. It can be considered as low-level extension which provide access to OpenCart API to make prototype, build minimum viable product or specifically custom function.</p>
        <hr style="margin:20px 0;">
        <p class="text-center mb-20">
            v<?php echo $architect['version']; ?> •
            <a href="https://github.com/iSenseLabs/architect" target="_blank">Project</a> •
            <a href="https://github.com/iSenseLabs/architect/wiki" target="_blank">Wiki</a> •
            <a href="https://github.com/iSenseLabs/architect/issues" target="_blank">Issues</a> •
            <a href="https://github.com/iSenseLabs/architect/blob/master/LICENSE" target="_blank">GPLv3+</a>
        </p>
    </div>

    <div class="col-md-7">
        <div class="row">
            <div class="col-sm-6">
                <div class="thumbnail">
                    <img src="view/javascript/architect/image/tickets.png">
                    <div class="caption" style="text-align:center;padding-top:0px;">
                        <h3><?php echo $i18n['text_tickets']; ?></h3>
                        <p><?php echo $i18n['text_open_ticket_info']; ?></p>
                        <p style="padding-top: 15px;">
                            <a href="<?php echo $urlTicketSupport; ?>" target="_blank" class="btn btn-lg btn-primary"><?php echo $i18n['text_open_ticket']; ?></a>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="thumbnail">
                    <img alt="Pre-sale support" style="width: 300px;" src="view/javascript/architect/image/pre-sale.png">
                    <div class="caption" style="text-align:center;padding-top:0px;">
                        <h3><?php echo $i18n['text_pre_sale']; ?></h3>
                        <p><?php echo $i18n['text_pre_sale_info']; ?></p>
                        <p style="padding-top: 15px;">
                            <a href="mailto:sales@isenselabs.com?subject=Pre-sale question" target="_blank" class="btn btn-lg btn-primary"><?php echo $i18n['text_bump_the_sales']; ?></a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

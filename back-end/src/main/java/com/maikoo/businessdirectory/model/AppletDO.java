package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.query.AppletQuery;
import lombok.Data;

@Data
public class AppletDO {
    private String miniName;
    private String shareText;
    private String LBSKey;

    public static AppletDO valueOf(AppletQuery appletQuery) {
        AppletDO appletDO = new AppletDO();
        appletDO.setLBSKey(appletQuery.getLBSKey());
        appletDO.setMiniName(appletQuery.getMiniName());
        appletDO.setShareText(appletQuery.getShareText());
        return appletDO;
    }
}

package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.query.FormIdQuery;
import lombok.Data;

@Data
public class FormIdDO {
    private long idx;
    private long userId;
    private String formId;
    private long expireAt;
    private boolean isUsed;

    public static FormIdDO valueOf(FormIdQuery formIdQuery){
        FormIdDO formIdDO = new FormIdDO();
        formIdDO.setFormId(formIdQuery.getFormId());
        formIdDO.setExpireAt(formIdQuery.getExpireAt());
        return formIdDO;
    }
}

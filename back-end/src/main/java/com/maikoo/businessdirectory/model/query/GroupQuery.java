package com.maikoo.businessdirectory.model.query;

import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import lombok.Data;

@Data
public class GroupQuery {
    private GroupTypeEnum groupType;
    private long groupId;
    private int pageNum;
    private long userId;
}

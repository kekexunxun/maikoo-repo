package com.maikoo.businessdirectory.model.query;

import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.constant.GroupUserActionEnum;
import com.maikoo.businessdirectory.model.constant.ReviewStatusEnum;
import lombok.Data;

@Data
public class GroupUserQuery {
    private GroupTypeEnum groupType;
    private long groupId;
    private long userId;
    private long applyId;
    private ReviewStatusEnum result;

    private String name;
    private String mobile;
    private String gender;
    private String position;
    private String company;
    private String brief;
    private String tag;
    private int building;
    private int room;
    private int graduateAt;

    private String userType;

    private boolean hasDetail;

    private int pageNum;
    private boolean isRequiredPaging;

    private GroupUserActionEnum action;

    private String search;
}

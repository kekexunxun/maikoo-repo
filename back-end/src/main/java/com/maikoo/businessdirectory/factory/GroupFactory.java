package com.maikoo.businessdirectory.factory;

import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.service.GroupService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

@Component
public class GroupFactory {
    @Autowired
    private GroupService classGroupService;
    @Autowired
    private GroupService schoolGroupService;
    @Autowired
    private GroupService countryGroupService;
    @Autowired
    private GroupService communityGroupService;

    public GroupService getGroup(GroupTypeEnum groupTypeEnum){
        GroupService groupService = null;
        switch (groupTypeEnum){
            case CLASS:
                groupService = classGroupService;
                break;
            case SCHOOL:
                groupService = schoolGroupService;
                break;
            case COUNTRY:
                groupService = countryGroupService;
                break;
            case COMMUNITY:
                groupService = communityGroupService;
                break;
        }
        return groupService;
    }
}

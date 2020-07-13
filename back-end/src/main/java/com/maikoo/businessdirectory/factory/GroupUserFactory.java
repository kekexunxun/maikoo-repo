package com.maikoo.businessdirectory.factory;

import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.service.GroupUserService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Component;

@Component
public class GroupUserFactory {
    @Autowired
    private GroupUserService schoolGroupUserService;
    @Autowired
    private GroupUserService classGroupUserService;
    @Autowired
    private GroupUserService countryGroupUserService;
    @Autowired
    private GroupUserService communityGroupUserService;

    public GroupUserService getGroupUserService(GroupTypeEnum groupTypeEnum){
        GroupUserService groupUserService = null;
        switch (groupTypeEnum){
            case SCHOOL:
                groupUserService = schoolGroupUserService;
                break;
            case CLASS:
                groupUserService = classGroupUserService;
                break;
            case COUNTRY:
                groupUserService = countryGroupUserService;
                break;
            case COMMUNITY:
                groupUserService = communityGroupUserService;
                break;
        }
        return groupUserService;
    }
}

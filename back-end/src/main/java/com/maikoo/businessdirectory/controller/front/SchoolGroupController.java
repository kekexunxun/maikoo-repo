package com.maikoo.businessdirectory.controller.front;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.factory.GroupFactory;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.SchoolGroupInformationQuery;
import com.maikoo.businessdirectory.model.view.GroupView;
import com.maikoo.businessdirectory.service.GroupService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@ResponseBody
@Controller
@RequestMapping("/api/group/school")
public class SchoolGroupController {
    private GroupService groupService;

    @Autowired
    public void setGroupService(GroupFactory groupFactory) {
        this.groupService = groupFactory.getGroup(GroupTypeEnum.SCHOOL);
    }

    @JsonView(GroupView.Insert.class)
    @RequestMapping("/add")
    public ResponseDTO<GroupDTO> insert(@Validated SchoolGroupInformationQuery schoolGroupInformationQuery) {
        return new ResponseDTO<>(200, "success", groupService.insert(schoolGroupInformationQuery));
    }

    @RequestMapping("/update")
    public ResponseDTO update(@Validated SchoolGroupInformationQuery schoolGroupInformationQuery) {
        groupService.update(schoolGroupInformationQuery);
        return new ResponseDTO(200, "success");
    }
}

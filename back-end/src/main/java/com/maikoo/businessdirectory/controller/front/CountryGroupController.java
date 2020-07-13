package com.maikoo.businessdirectory.controller.front;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.factory.GroupFactory;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.CountryGroupInformationQuery;
import com.maikoo.businessdirectory.model.view.GroupView;
import com.maikoo.businessdirectory.service.GroupService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@ResponseBody
@Controller
@RequestMapping("/api/group/country")
public class CountryGroupController {
    private GroupService groupService;

    @Autowired
    public void setGroupService(GroupFactory groupFactory) {
        this.groupService = groupFactory.getGroup(GroupTypeEnum.COUNTRY);
    }

    @JsonView(GroupView.Insert.class)
    @RequestMapping("/add")
    public ResponseDTO<GroupDTO> insert(@Validated CountryGroupInformationQuery countryGroupInformationQuery) {
        return new ResponseDTO<>(200, "success", groupService.insert(countryGroupInformationQuery));
    }

    @RequestMapping("/update")
    public ResponseDTO update(@Validated CountryGroupInformationQuery countryGroupInformationQuery) {
        groupService.update(countryGroupInformationQuery);
        return new ResponseDTO(200, "success");
    }
}

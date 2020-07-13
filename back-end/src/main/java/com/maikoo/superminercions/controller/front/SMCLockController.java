package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.exception.InvalidParameterException;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.dto.SMCLockCycleDTO;
import com.maikoo.superminercions.model.dto.SMCLockDTO;
import com.maikoo.superminercions.model.dto.SMCLockInformationDTO;
import com.maikoo.superminercions.service.SMCLockService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import java.math.BigDecimal;
import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/smc/lock")
public class SMCLockController {
    @Autowired
    private SMCLockService smcLockService;

    @RequestMapping("/cycle")
    public ResponseDTO<List<SMCLockCycleDTO>> lockCycle() {
        return new ResponseDTO<>(200, "获取成功", smcLockService.lockCycle());
    }

    @RequestMapping("/apply")
    public ResponseDTO apply(@RequestParam("lpId") long lockCycleId, @RequestParam("smcNum") BigDecimal smcQuantity) {
        if(lockCycleId < 1 || smcQuantity.compareTo(new BigDecimal(0.000001)) < 0){
            throw new InvalidParameterException("参数不正确");
        }
        smcLockService.apply(lockCycleId, smcQuantity);
        return new ResponseDTO(200, "锁仓成功", null);
    }

    @RequestMapping
    public ResponseDTO<List<SMCLockDTO>> list(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber){
        return new ResponseDTO<>(200, "获取成功", smcLockService.list(pageNumber));
    }

    @RequestMapping("/information")
    public ResponseDTO<SMCLockInformationDTO> information(@RequestParam(name = "listSn") long orderSN){
        return new ResponseDTO<>(200, "获取成功", smcLockService.information(orderSN));
    }
}

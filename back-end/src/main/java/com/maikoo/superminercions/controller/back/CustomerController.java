package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.*;
import com.maikoo.superminercions.model.query.CustomerQuery;
import com.maikoo.superminercions.model.query.OrderQuery;
import com.maikoo.superminercions.service.CustomerService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller("AdminCustomerController")
@ResponseBody
@RequestMapping("/api/admin/customer")
public class CustomerController {
    @Autowired
    private CustomerService customerService;

    @RequestMapping
    public ResponseDTO<List<CustomerDTO>> list() {
        return new ResponseDTO<>(200, "获取成功", customerService.list());
    }

    @RequestMapping("/add")
    public ResponseDTO add(CustomerQuery customerQuery) {
        customerService.add(customerQuery);
        return new ResponseDTO(200, "新增成功", null);
    }

    @RequestMapping("/update")
    public ResponseDTO update(CustomerQuery customerQuery) {
        customerService.update(customerQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/status/update")
    public ResponseDTO updateStatus(@RequestParam("userId") long id, @RequestParam("action") String status) {
        customerService.updateStatus(id, status);
        return new ResponseDTO(200, "变更成功", null);
    }

    @RequestMapping("/balance")
    public ResponseDTO<List<CustomerBalanceWithCustomerDTO>> balanceList() {
        return new ResponseDTO<>(200, "获取成功", customerService.balanceListAll());
    }

    @RequestMapping("/base")
    public ResponseDTO<List<CustomerBaseDTO>> baseList() {
        return new ResponseDTO<>(200, "获取成功", customerService.baseListAll());
    }

    @RequestMapping("/base/information")
    public ResponseDTO<CustomerBaseInformationDTO> baseInformation(@RequestParam("userId") long id) {
        return new ResponseDTO<>(200, "获取成功", customerService.baseInformation(id));
    }

    @RequestMapping("/product")
    public ResponseDTO<List<CustomerProductWithCustomerDTO>> productList() {
        return new ResponseDTO<>(200, "获取成功", customerService.productListAll());
    }

    @RequestMapping("/product/status/update")
    public ResponseDTO updateProductStatus(@RequestParam("listSn") long customerProductId, @RequestParam("action") String status) {
        customerService.updateProductStatus(customerProductId, status);
        return new ResponseDTO(200, "修改成功", null);
    }

//    @RequestMapping("/product/update")
//    public ResponseDTO updateProduct(CustomerProductQuery customerProductQuery) {
//        customerService.updateProduct(customerProductQuery);
//        return new ResponseDTO(200, "修改成功", null);
//    }

    @RequestMapping("/product/apply")
    public ResponseDTO<List<CustomerProductApplyDTO>> productApplyList() {
        return new ResponseDTO<>(200, "获取成功", customerService.productApplyListAll());
    }

    @RequestMapping("/product/apply/update")
    public ResponseDTO updateProductApplyStatus(OrderQuery orderQuery) {
        customerService.updateProductApplyStatus(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }

    @RequestMapping("/product/apply/remark/update")
    public ResponseDTO updateProductApplyNote(OrderQuery orderQuery) {
        customerService.updateProductApplyNote(orderQuery);
        return new ResponseDTO(200, "修改成功", null);
    }
}

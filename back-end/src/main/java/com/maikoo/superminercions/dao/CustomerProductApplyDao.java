package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.CustomerProductApplyDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Mapper;
import org.apache.ibatis.annotations.ResultMap;
import org.apache.ibatis.annotations.Select;

import java.util.List;

@Mapper
public interface CustomerProductApplyDao {

    @ResultMap("customerProductApplyResultMap")
    @Select("SELECT " +
                "cu.name AS c_user_name, " +
                "cu.phone AS c_user_phone, " +
                "co.order_sn, " +
                "co.status, " +
                "co.note, " +
                "co.created_at, " +
                "co.updated_at, " +
                "cp.name AS comm_product_name, " +
                "cp.model AS comm_product_model, " +
                "cp.performance AS comm_product_performance, " +
                "cp.price AS comm_product_price " +
            "FROM " +
                "c_order co " +
                "JOIN c_user_product_apply cupa ON co.id = cupa.c_order_id " +
                "JOIN comm_product cp ON cupa.comm_product_id = cp.id " +
                "JOIN c_user cu ON co.c_user_id = cu.id " +
            "WHERE " +
                "co.deleted_at IS NULL")
    List<CustomerProductApplyDO> selectAllWithCustomer();

    @ResultMap("customerProductApplyResultMap")
    @Select("SELECT " +
                "cu.id AS c_user_id, " +
                "cu.name AS c_user_name, " +
                "cu.phone AS c_user_phone, " +
                "co.order_sn, " +
                "co.status, " +
                "co.note, " +
                "co.created_at, " +
                "co.updated_at, " +
                "cp.image_uri AS comm_product_image_uri, " +
                "cp.product_number AS comm_product_product_number, " +
                "cp.name AS comm_product_name, " +
                "cp.model AS comm_product_model, " +
                "cp.performance AS comm_product_performance, " +
                "cp.price AS comm_product_price " +
            "FROM " +
                "c_order co " +
                "JOIN c_user_product_apply cupa ON co.id = cupa.c_order_id " +
                "JOIN comm_product cp ON cupa.comm_product_id = cp.id " +
                "JOIN c_user cu ON co.c_user_id = cu.id " +
            "WHERE " +
                "co.deleted_at IS NULL " +
                "AND co.order_sn = #{orderSN}")
    CustomerProductApplyDO selectOne(long orderSN);

    @Insert("INSERT " +
            "INTO " +
                "c_user_product_apply" +
                "(c_order_id, comm_product_id) " +
            "VALUES" +
                "(#{id}, #{productDO.id})")
    int insert(CustomerProductApplyDO customerProductApplyDO);
}

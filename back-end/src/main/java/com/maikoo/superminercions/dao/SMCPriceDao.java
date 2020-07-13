package com.maikoo.superminercions.dao;

import com.maikoo.superminercions.model.SMCPriceDO;
import org.apache.ibatis.annotations.Insert;
import org.apache.ibatis.annotations.Mapper;
import org.apache.ibatis.annotations.Param;
import org.apache.ibatis.annotations.Update;

import java.util.List;

@Mapper
public interface SMCPriceDao {
    @Insert("INSERT INTO comm_smc_price(date, price) VALUES(#{date}, #{price})")
    int insert(SMCPriceDO smcPriceDO);

    @Update("UPDATE comm_smc_price SET price = #{price} WHERE id = #{id}")
    int update(SMCPriceDO smcPriceDO);

    SMCPriceDO selectOneByDate(long timeStamp);

    List<SMCPriceDO> selectByDate(@Param("beginTimeStamp") long beginTimeStamp, @Param("endTimeStamp") long endTimeStamp);
}
